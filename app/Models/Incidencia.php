<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incidencia extends Model
{
    // Mapear a la tabla existente
    protected $table = 'incidencias';

    // Llave primaria
    protected $primaryKey = 'id';

    // Desactivar timestamps automáticos de Eloquent
    public $timestamps = false;

    // Permitir asignación masiva
    protected $guarded = [];

    /**
     * Relación con el empleado (datos personales)
     */
    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'N_Empleado', 'N_Empleado');
    }
}
