<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancha_id')->constrained('canchas')->cascadeOnDelete();
            $table->date('fecha');
            // Bloques fijos de 1 hora: hora_fin siempre es hora_inicio + 1h, por eso no se guarda.
            $table->time('hora_inicio');
            $table->string('estado', 10);
            $table->string('cliente_nombre', 100);
            $table->string('cliente_telefono', 20);
            $table->text('nota')->nullable();
            // Admin que registró la reserva; si se borra el usuario la reserva sobrevive.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Defensa real contra doble reserva: la gana la base de datos, no el código.
            $table->unique(['cancha_id', 'fecha', 'hora_inicio']);
            $table->index(['cancha_id', 'fecha']);
        });

        DB::statement("ALTER TABLE reservas ADD CONSTRAINT reservas_estado_check CHECK (estado IN ('separado','ocupado'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
