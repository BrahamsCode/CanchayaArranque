<?php

namespace Tests\Feature;

use App\Models\Cancha;
use App\Models\Configuracion;
use App\Models\Reserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DisponibilidadPublicaTest extends TestCase
{
    use RefreshDatabase;

    private Cancha $cancha;

    private string $fecha;

    protected function setUp(): void
    {
        parent::setUp();

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

    private function reservar(string $hora, string $estado): Reserva
    {
        return Reserva::create([
            'cancha_id' => $this->cancha->id,
            'fecha' => $this->fecha,
            'hora_inicio' => $hora,
            'estado' => $estado,
            'cliente_nombre' => 'Luis Ramírez',
            'cliente_telefono' => '51987111222',
            'nota' => 'Pagó adelanto',
        ]);
    }

    public function test_configuracion_publica_expone_negocio_y_canchas_activas(): void
    {
        Cancha::create(['nombre' => 'Cancha inactiva', 'activa' => false]);

        $this->getJson('/api/configuracion-publica')
            ->assertOk()
            ->assertJson([
                'nombre_negocio' => 'CanchaYa',
                'whatsapp' => '51987654321',
                'hora_apertura' => '08:00',
                'hora_cierre' => '23:00',
                'precio_hora' => '60.00',
                'canchas' => [['id' => $this->cancha->id, 'nombre' => 'Cancha 1']],
            ])
            ->assertJsonCount(1, 'canchas');
    }

    public function test_disponibilidad_devuelve_slots_segun_horario_y_marca_estados(): void
    {
        $this->reservar('09:00', Reserva::ESTADO_SEPARADO);
        $this->reservar('10:00', Reserva::ESTADO_OCUPADO);

        $respuesta = $this->getJson("/api/disponibilidad?cancha_id={$this->cancha->id}&fecha={$this->fecha}")
            ->assertOk()
            ->assertJson(['fecha' => $this->fecha, 'cancha_id' => $this->cancha->id]);

        // 08:00 a 23:00 en bloques de 1h = 15 slots; el último es 22:00-23:00.
        $respuesta->assertJsonCount(15, 'slots');

        $slots = collect($respuesta->json('slots'))->keyBy('hora_inicio');

        $this->assertSame(['hora_inicio' => '08:00', 'hora_fin' => '09:00', 'estado' => 'libre'], $slots['08:00']);
        $this->assertSame('separado', $slots['09:00']['estado']);
        $this->assertSame('ocupado', $slots['10:00']['estado']);
        $this->assertSame(['hora_inicio' => '22:00', 'hora_fin' => '23:00', 'estado' => 'libre'], $slots['22:00']);
    }

    public function test_disponibilidad_nunca_expone_datos_del_cliente(): void
    {
        $this->reservar('09:00', Reserva::ESTADO_OCUPADO);

        $respuesta = $this->getJson("/api/disponibilidad?cancha_id={$this->cancha->id}&fecha={$this->fecha}")
            ->assertOk()
            ->assertJsonMissing(['cliente_nombre' => 'Luis Ramírez'])
            ->assertJsonMissing(['cliente_telefono' => '51987111222']);

        foreach ($respuesta->json('slots') as $slot) {
            $this->assertSame(['hora_inicio', 'hora_fin', 'estado'], array_keys($slot));
        }

        $this->assertStringNotContainsString('Luis', $respuesta->getContent());
        $this->assertStringNotContainsString('51987111222', $respuesta->getContent());
    }

    public function test_cambiar_el_horario_cambia_la_cantidad_de_slots(): void
    {
        Configuracion::obtener()->update(['hora_apertura' => '10:00', 'hora_cierre' => '14:00']);

        $this->getJson("/api/disponibilidad?cancha_id={$this->cancha->id}&fecha={$this->fecha}")
            ->assertOk()
            ->assertJsonCount(4, 'slots')
            ->assertJsonPath('slots.0.hora_inicio', '10:00')
            ->assertJsonPath('slots.3.hora_inicio', '13:00')
            ->assertJsonPath('slots.3.hora_fin', '14:00');
    }

    public function test_disponibilidad_de_cancha_inexistente_o_inactiva_da_404(): void
    {
        $inactiva = Cancha::create(['nombre' => 'Cancha 2', 'activa' => false]);

        $this->getJson("/api/disponibilidad?cancha_id={$inactiva->id}&fecha={$this->fecha}")
            ->assertNotFound()
            ->assertJsonStructure(['mensaje']);

        $this->getJson('/api/disponibilidad?cancha_id=9999')->assertNotFound();
    }
}
