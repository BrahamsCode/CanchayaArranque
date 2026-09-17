<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Cancha;
use App\Support\GeneradorSlots;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DisponibilidadController extends Controller
{
    /**
     * Grilla pública de horas. Nunca expone datos del cliente:
     * solo libre / separado / ocupado.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'fecha' => ['nullable', 'date_format:Y-m-d'],
            'cancha_id' => ['nullable', 'integer'],
        ], [
            'fecha.date_format' => 'La fecha debe tener el formato AAAA-MM-DD.',
            'cancha_id.integer' => 'La cancha debe ser un identificador válido.',
        ]);

        $fecha = $datos['fecha'] ?? Carbon::today()->toDateString();

        $cancha = isset($datos['cancha_id'])
            ? Cancha::activas()->find($datos['cancha_id'])
            : Cancha::activas()->orderBy('id')->first();

        if (! $cancha) {
            return response()->json(['mensaje' => 'La cancha no existe o no está activa.'], 404);
        }

        return response()->json([
            'fecha' => $fecha,
            'cancha_id' => $cancha->id,
            'slots' => GeneradorSlots::construir($cancha, $fecha),
        ]);
    }
}
