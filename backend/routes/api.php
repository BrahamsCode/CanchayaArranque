<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\ReservaController;
use App\Http\Controllers\Public\ConfiguracionPublicaController;
use App\Http\Controllers\Public\DisponibilidadController;
use Illuminate\Support\Facades\Route;

// Público: sin auth, sin datos de clientes.
Route::get('/configuracion-publica', ConfiguracionPublicaController::class);
Route::get('/disponibilidad', DisponibilidadController::class);

// Login limitado para frenar fuerza bruta.
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/reservas', [ReservaController::class, 'index']);
    Route::post('/reservas', [ReservaController::class, 'store']);
    Route::patch('/reservas/{reserva}', [ReservaController::class, 'update']);
    Route::delete('/reservas/{reserva}', [ReservaController::class, 'destroy']);

    Route::get('/configuracion', [ConfiguracionController::class, 'show']);
    Route::put('/configuracion', [ConfiguracionController::class, 'update']);
});
