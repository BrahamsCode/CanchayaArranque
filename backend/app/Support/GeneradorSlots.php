<?php

namespace App\Support;

use App\Models\Cancha;
use App\Models\Configuracion;
use App\Models\Reserva;

/**
 * Único lugar donde se arma la grilla de bloques de 1 hora.
 * Lo usan por igual la vista pública y la de admin; lo que cambia es
 * si se exponen o no los datos del cliente.
 */
class GeneradorSlots
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function construir(Cancha $cancha, string $fecha, bool $conDatosPrivados = false): array
    {
        $config = Configuracion::obtener();

        $reservas = Reserva::where('cancha_id', $cancha->id)
            ->whereDate('fecha', $fecha)
            ->get()
            ->keyBy(fn (Reserva $r) => $r->hora_inicio);

        $slots = [];

        foreach (self::horas($config->hora_apertura, $config->hora_cierre) as $hora) {
            $reserva = $reservas->get($hora);

            $slot = [
                'hora_inicio' => $hora,
                'hora_fin' => self::sumarHora($hora),
                'estado' => $reserva?->estado ?? 'libre',
            ];

            if ($conDatosPrivados) {
                $slot += [
                    'id' => $reserva?->id,
                    'cliente_nombre' => $reserva?->cliente_nombre,
                    'cliente_telefono' => $reserva?->cliente_telefono,
                    'nota' => $reserva?->nota,
                ];
            }

            $slots[] = $slot;
        }

        return $slots;
    }

    /**
     * Horas de inicio en punto; el último bloque arranca en cierre - 1h.
     *
     * @return array<int, string>
     */
    public static function horas(string $apertura, string $cierre): array
    {
        $inicio = self::aMinutos($apertura);
        $fin = self::aMinutos($cierre);

        $horas = [];
        for ($m = $inicio; $m + 60 <= $fin; $m += 60) {
            $horas[] = self::aTexto($m);
        }

        return $horas;
    }

    public static function sumarHora(string $hora): string
    {
        return self::aTexto(self::aMinutos($hora) + 60);
    }

    private static function aMinutos(string $hora): int
    {
        [$h, $m] = array_map('intval', explode(':', substr($hora, 0, 5)));

        return $h * 60 + $m;
    }

    private static function aTexto(int $minutos): string
    {
        return sprintf('%02d:%02d', intdiv($minutos, 60) % 24, $minutos % 60);
    }
}
