<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de una sola fila (id = 1); por eso el id no es autoincremental.
        Schema::create('configuracion', function (Blueprint $table) {
            $table->smallInteger('id')->primary();
            $table->string('nombre_negocio', 100);
            $table->string('whatsapp', 20);
            $table->time('hora_apertura');
            $table->time('hora_cierre');
            $table->decimal('precio_hora', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
