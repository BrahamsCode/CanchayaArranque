<?php

namespace Database\Seeders;

use App\Models\Cancha;
use App\Models\Configuracion;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    // Todo con updateOrCreate para poder re-sembrar sin duplicar nada.
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@canchaya.test'],
            ['name' => 'Administrador', 'password' => Hash::make('password')]
        );

        $cancha = Cancha::updateOrCreate(
            ['nombre' => 'Cancha 1'],
            ['activa' => true]
        );

        Configuracion::updateOrCreate(['id' => 1], [
            'nombre_negocio' => 'CanchaYa',
            'whatsapp' => '51987654321',
            'hora_apertura' => '08:00',
            'hora_cierre' => '23:00',
            'precio_hora' => '60.00',
        ]);

        $hoy = Carbon::today()->toDateString();
        $manana = Carbon::tomorrow()->toDateString();

        $ejemplos = [
            [$hoy, '19:00', Reserva::ESTADO_OCUPADO, 'Luis Ramírez', '51987111222', 'Pagó adelanto'],
            [$hoy, '21:00', Reserva::ESTADO_SEPARADO, 'Carla Díaz', '51987333444', null],
            [$manana, '18:00', Reserva::ESTADO_SEPARADO, 'Jorge Peña', '51987555666', 'Confirma en la tarde'],
            [$manana, '20:00', Reserva::ESTADO_OCUPADO, 'Equipo Los Andes', '51987777888', null],
        ];

        foreach ($ejemplos as [$fecha, $hora, $estado, $nombre, $telefono, $nota]) {
            Reserva::updateOrCreate(
                ['cancha_id' => $cancha->id, 'fecha' => $fecha, 'hora_inicio' => $hora],
                [
                    'estado' => $estado,
                    'cliente_nombre' => $nombre,
                    'cliente_telefono' => $telefono,
                    'nota' => $nota,
                    'user_id' => $admin->id,
                ]
            );
        }
    }
}
