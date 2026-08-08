<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Candidato extends Model
{
    protected $table = 'candidatos';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialEstado::class, 'candidato_id', 'id')->orderByDesc('fecha');
    }

    public function ultimoHistorial(): HasOne
    {
        return $this->hasOne(HistorialEstado::class, 'candidato_id', 'id')->latestOfMany('fecha');
    }
}
