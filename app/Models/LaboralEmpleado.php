<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboralEmpleado extends Model
{
    // Mapear a la tabla existente
    protected $table = 'laborales_empleado';

    // Llave primaria personalizada
    protected $primaryKey = 'ID';

    // Desactivar timestamps automáticos (created_at, updated_at)
    public $timestamps = false;

    // Permitir asignación masiva de campos
    protected $guarded = [];

    /**
     * Relación inversa con los datos personales
     */
    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'N_Empleado', 'N_Empleado');
    }
}
