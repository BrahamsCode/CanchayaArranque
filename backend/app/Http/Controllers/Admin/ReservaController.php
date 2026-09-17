<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarReservaRequest;
use App\Models\Cancha;
use App\Models\Reserva;
use App\Support\GeneradorSlots;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    /** Violación de UNIQUE en Postgres. */
    private const SQLSTATE_DUPLICADO = '23505';

    /** Misma grilla que la vista pública, pero con los datos del cliente. */
    public function index(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'fecha' => ['nullable', 'date_format:Y-m-d'],
            'cancha_id' => ['nullable', 'integer'],
        ], ['fecha.date_format' => 'La fecha debe tener el formato AAAA-MM-DD.']);

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
            'slots' => GeneradorSlots::construir($cancha, $fecha, conDatosPrivados: true),
        ]);
    }

    public function store(GuardarReservaRequest $request): JsonResponse
    {
        $datos = $request->validated() + ['user_id' => $request->user()->id];

        return $this->sinChoques(fn () => response()->json(
            DB::transaction(fn () => Reserva::create($datos)),
            201
        ));
    }

    public function update(GuardarReservaRequest $request, Reserva $reserva): JsonResponse
    {
        return $this->sinChoques(fn () => response()->json(
            DB::transaction(function () use ($request, $reserva) {
                $reserva->update($request->validated());

                return $reserva;
            })
        ));
    }

    /** Liberar la hora = borrar la fila; si no hay fila, la hora está libre. */
    public function destroy(Reserva $reserva): JsonResponse
    {
        DB::transaction(fn () => $reserva->delete());

        return response()->json(['mensaje' => 'Reserva liberada.']);
    }

    /**
     * Dejamos que el UNIQUE de la base decida quién gana la carrera:
     * un "exists" previo no sirve con dos admins guardando a la vez.
     */
    private function sinChoques(callable $accion): JsonResponse
    {
        try {
            return $accion();
        } catch (QueryException $e) {
            if ($e->getCode() === self::SQLSTATE_DUPLICADO) {
                return response()->json(['mensaje' => 'Esa hora ya está tomada'], 409);
            }

            throw $e;
        }
    }
}
