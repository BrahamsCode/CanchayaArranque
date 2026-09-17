<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Cancha extends Model
{
    protected $table = 'canchas';

    protected $fillable = ['nombre', 'activa'];

    protected $casts = ['activa' => 'boolean'];

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true);
    }
}
