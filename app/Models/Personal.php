<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Personal extends Model
{
    // Mapear a la tabla existente
    protected $table = 'personales';

    // Llave primaria personalizada
    protected $primaryKey = 'ID';

    // Desactivar timestamps automáticos (created_at, updated_at)
    public $timestamps = false;

    // Permitir asignación masiva de campos
    protected $guarded = [];

    /**
     * Relación uno a uno con la información laboral
     */
    public function laboral(): HasOne
    {
        return $this->hasOne(LaboralEmpleado::class, 'N_Empleado', 'N_Empleado');
    }
}
