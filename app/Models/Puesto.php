<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Puesto extends Model
{
    protected $table = 'puestos';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function laborales(): HasMany
    {
        return $this->hasMany(LaboralEmpleado::class, 'ID_Puesto', 'ID');
    }
}
