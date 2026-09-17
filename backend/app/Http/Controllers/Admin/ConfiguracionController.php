<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarConfiguracionRequest;
use App\Models\Configuracion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json($this->formato(Configuracion::obtener()));
    }

    public function update(GuardarConfiguracionRequest $request): JsonResponse
    {
        $config = DB::transaction(function () use ($request) {
            $config = Configuracion::obtener();
            $config->update($request->validated());

            return $config;
        });

        return response()->json($this->formato($config->refresh()));
    }

    private function formato(Configuracion $config): array
    {
        return [
            'nombre_negocio' => $config->nombre_negocio,
            'whatsapp' => $config->whatsapp,
            'hora_apertura' => $config->hora_apertura,
            'hora_cierre' => $config->hora_cierre,
            'precio_hora' => $config->precio_hora,
        ];
    }
}
