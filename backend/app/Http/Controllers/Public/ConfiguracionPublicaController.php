<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Cancha;
use App\Models\Configuracion;
use Illuminate\Http\JsonResponse;

class ConfiguracionPublicaController extends Controller
{
    /** Datos que el frontend necesita para dibujar la pantalla pública. */
    public function __invoke(): JsonResponse
    {
        $config = Configuracion::obtener();

        return response()->json([
            'nombre_negocio' => $config->nombre_negocio,
            'whatsapp' => $config->whatsapp,
            'hora_apertura' => $config->hora_apertura,
            'hora_cierre' => $config->hora_cierre,
            'precio_hora' => $config->precio_hora,
            'canchas' => Cancha::activas()
                ->orderBy('id')
                ->get(['id', 'nombre'])
                ->map(fn (Cancha $c) => ['id' => $c->id, 'nombre' => $c->nombre]),
        ]);
    }
}
