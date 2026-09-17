<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracion';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'nombre_negocio',
        'whatsapp',
        'hora_apertura',
        'hora_cierre',
        'precio_hora',
    ];

    /** La configuración es una única fila con id = 1. */
    public static function obtener(): self
    {
        // firstOrCreate para que la API nunca reviente si la fila todavía no existe.
        return static::firstOrCreate(['id' => 1], [
            'nombre_negocio' => 'CanchaYa',
            'whatsapp' => '51987654321',
            'hora_apertura' => '08:00',
            'hora_cierre' => '23:00',
            'precio_hora' => '60.00',
        ]);
    }

    protected function horaApertura(): Attribute
    {
        return Attribute::make(get: fn (?string $v) => $v === null ? null : substr($v, 0, 5));
    }

    protected function horaCierre(): Attribute
    {
        return Attribute::make(get: fn (?string $v) => $v === null ? null : substr($v, 0, 5));
    }
}
