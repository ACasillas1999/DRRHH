<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoEstimado extends Model
{
    protected $table = 'empleados_estimados';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
