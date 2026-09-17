<?php

namespace App\Models;

use App\Support\GeneradorSlots;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    public const ESTADO_SEPARADO = 'separado';

    public const ESTADO_OCUPADO = 'ocupado';

    public const ESTADOS = [self::ESTADO_SEPARADO, self::ESTADO_OCUPADO];

    protected $table = 'reservas';

    protected $fillable = [
        'cancha_id',
        'fecha',
        'hora_inicio',
        'estado',
        'cliente_nombre',
        'cliente_telefono',
        'nota',
        'user_id',
    ];

    protected $casts = ['fecha' => 'date:Y-m-d'];

    /**
     * Postgres devuelve la hora como "18:00:00" pero toda la API habla en "HH:mm".
     * Normalizamos en el modelo para no repetir substr() en cada controlador.
     */
    protected function horaInicio(): Attribute
    {
        return Attribute::make(
            get: fn (?string $valor) => $valor === null ? null : substr($valor, 0, 5),
        );
    }

    /** Hora de fin derivada: los bloques siempre duran 1 hora. */
    public function horaFin(): string
    {
        return GeneradorSlots::sumarHora($this->hora_inicio);
    }

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
