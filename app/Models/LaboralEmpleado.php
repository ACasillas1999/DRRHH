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

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'ID_Puesto', 'ID');
    }

    public function razonSocial(): BelongsTo
    {
        // ojo: laborales_empleado.Nomeclatura vs razones_sociales.Nomenclatura (ortografía distinta en cada tabla)
        return $this->belongsTo(RazonSocial::class, 'Nomeclatura', 'Nomenclatura');
    }
}
