<?php

namespace Tests\Feature;

use App\Models\Cancha;
use App\Models\Configuracion;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReservasAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Cancha $cancha;

    private string $fecha;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@canchaya.test',
            'password' => Hash::make('password'),
        ]);

        $this->cancha = Cancha::create(['nombre' => 'Cancha 1', 'activa' => true]);
        $this->fecha = Carbon::tomorrow()->toDateString();

        Configuracion::create([
            'id' => 1,
            'nombre_negocio' => 'CanchaYa',
            'whatsapp' => '51987654321',
            'hora_apertura' => '08:00',
            'hora_cierre' => '23:00',
            'precio_hora' => '60.00',
        ]);
    }

    private function token(): string
    {
        return $this->postJson('/api/admin/login', [
            'email' => 'admin@canchaya.test',
            'password' => 'password',
        ])->json('token');
    }

    private function datosReserva(array $extra = []): array
    {
        return array_merge([
            'cancha_id' => $this->cancha->id,
            'fecha' => $this->fecha,
            'hora_inicio' => '19:00',
            'estado' => Reserva::ESTADO_SEPARADO,
            'cliente_nombre' => 'Luis Ramírez',
            'cliente_telefono' => '51987111222',
            'nota' => 'Pagó adelanto',
        ], $extra);
    }

    public function test_login_devuelve_token_y_usuario(): void
    {
        $respuesta = $this->postJson('/api/admin/login', [
            'email' => 'admin@canchaya.test',
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);

        $this->assertNotEmpty($respuesta->json('token'));
        $this->assertSame('admin@canchaya.test', $respuesta->json('user.email'));
    }

    public function test_credenciales_invalidas_dan_422(): void
    {
        $this->postJson('/api/admin/login', [
            'email' => 'admin@canchaya.test',
            'password' => 'incorrecta',
        ])->assertStatus(422)->assertJsonPath('errors.email.0', 'Las credenciales no son correctas.');
    }

    public function test_rutas_admin_sin_token_dan_401(): void
    {
        $this->getJson('/api/admin/reservas')->assertUnauthorized();
        $this->postJson('/api/admin/reservas', $this->datosReserva())->assertUnauthorized();
        $this->getJson('/api/admin/configuracion')->assertUnauthorized();
    }

    public function test_admin_crea_separado_pasa_a_ocupado_y_libera(): void
    {
        $token = $this->token();

        $creada = $this->withToken($token)
            ->postJson('/api/admin/reservas', $this->datosReserva())
            ->assertCreated()
            ->assertJson([
                'cancha_id' => $this->cancha->id,
                'fecha' => $this->fecha,
                'hora_inicio' => '19:00',
                'estado' => 'separado',
                'cliente_nombre' => 'Luis Ramírez',
                'user_id' => $this->admin->id,
            ]);

        $id = $creada->json('id');

        $this->withToken($token)
            ->patchJson("/api/admin/reservas/{$id}", ['estado' => Reserva::ESTADO_OCUPADO])
            ->assertOk()
            ->assertJsonPath('estado', 'ocupado');

        $this->withToken($token)->deleteJson("/api/admin/reservas/{$id}")->assertOk();

        $this->assertDatabaseCount('reservas', 0);
    }

    public function test_crear_dos_veces_la_misma_hora_devuelve_409(): void
    {
        $token = $this->token();

        $this->withToken($token)->postJson('/api/admin/reservas', $this->datosReserva())->assertCreated();

        $this->withToken($token)
            ->postJson('/api/admin/reservas', $this->datosReserva(['cliente_nombre' => 'Otro cliente']))
            ->assertStatus(409)
            ->assertExactJson(['mensaje' => 'Esa hora ya está tomada']);

        $this->assertDatabaseCount('reservas', 1);
    }

    public function test_mover_con_patch_a_una_hora_tomada_devuelve_409(): void
    {
        $token = $this->token();

        $this->withToken($token)->postJson('/api/admin/reservas', $this->datosReserva())->assertCreated();
        $segunda = $this->withToken($token)
            ->postJson('/api/admin/reservas', $this->datosReserva(['hora_inicio' => '20:00']))
            ->assertCreated();

        $this->withToken($token)
            ->patchJson("/api/admin/reservas/{$segunda->json('id')}", ['hora_inicio' => '19:00'])
            ->assertStatus(409);
    }

    public function test_crear_en_fecha_pasada_falla_con_422(): void
    {
        $this->withToken($this->token())
            ->postJson('/api/admin/reservas', $this->datosReserva([
                'fecha' => Carbon::yesterday()->toDateString(),
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('fecha');
    }

    public function test_crear_fuera_del_horario_falla_con_422(): void
    {
        $this->withToken($this->token())
            ->postJson('/api/admin/reservas', $this->datosReserva(['hora_inicio' => '23:00']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('hora_inicio');
    }

    public function test_listado_admin_si_incluye_nombre_y_telefono(): void
    {
        $token = $this->token();

        $this->withToken($token)->postJson('/api/admin/reservas', $this->datosReserva())->assertCreated();

        $respuesta = $this->withToken($token)
            ->getJson("/api/admin/reservas?cancha_id={$this->cancha->id}&fecha={$this->fecha}")
            ->assertOk()
            ->assertJsonCount(15, 'slots');

        $slots = collect($respuesta->json('slots'))->keyBy('hora_inicio');

        $this->assertSame('Luis Ramírez', $slots['19:00']['cliente_nombre']);
        $this->assertSame('51987111222', $slots['19:00']['cliente_telefono']);
        $this->assertSame('Pagó adelanto', $slots['19:00']['nota']);
        $this->assertNotNull($slots['19:00']['id']);

        // Los slots libres traen los mismos campos en null, para que el frontend no tenga que adivinar.
        $this->assertNull($slots['08:00']['id']);
        $this->assertNull($slots['08:00']['cliente_nombre']);
        $this->assertSame('libre', $slots['08:00']['estado']);
    }

    public function test_admin_lee_y_actualiza_la_configuracion(): void
    {
        $token = $this->token();

        $this->withToken($token)->getJson('/api/admin/configuracion')
            ->assertOk()
            ->assertJsonPath('nombre_negocio', 'CanchaYa');

        $this->withToken($token)->putJson('/api/admin/configuracion', [
            'nombre_negocio' => 'Cancha del Barrio',
            'whatsapp' => '51900111222',
            'hora_apertura' => '09:00',
            'hora_cierre' => '22:00',
            'precio_hora' => '75.50',
        ])->assertOk()->assertJson([
            'nombre_negocio' => 'Cancha del Barrio',
            'hora_apertura' => '09:00',
            'hora_cierre' => '22:00',
        ]);

        $this->withToken($token)->putJson('/api/admin/configuracion', [
            'nombre_negocio' => 'Cancha del Barrio',
            'whatsapp' => '+51900111222',
            'hora_apertura' => '22:00',
            'hora_cierre' => '09:00',
        ])->assertStatus(422)->assertJsonValidationErrors(['whatsapp', 'hora_cierre']);
    }

    public function test_logout_revoca_el_token(): void
    {
        $token = $this->token();

        $this->withToken($token)->postJson('/api/admin/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // El guard cachea al usuario dentro del mismo test; lo olvidamos para simular una petición nueva.
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/admin/reservas')->assertUnauthorized();
    }
}
