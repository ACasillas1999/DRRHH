<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function rutaDocumentos(): HasOne
    {
        return $this->hasOne(RutaDocumento::class, 'N_Empleado', 'N_Empleado');
    }

    public function fechasLaborales(): HasMany
    {
        return $this->hasMany(FechaLaboral::class, 'N_Empleado', 'N_Empleado');
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class, 'N_Empleado', 'N_Empleado');
    }

    public function historialCambios(): HasMany
    {
        return $this->hasMany(HistorialCambio::class, 'N_Empleado', 'N_Empleado');
    }

    public function documentosBaja(): HasMany
    {
        return $this->hasMany(DocumentoBaja::class, 'N_Empleado', 'N_Empleado');
    }

    public function documentosGenerados(): HasMany
    {
        return $this->hasMany(DocumentoGenerado::class, 'N_Empleado', 'N_Empleado');
    }

    public function documentosImss(): HasMany
    {
        return $this->hasMany(DocumentoImss::class, 'n_empleado', 'N_Empleado');
    }

    public function documentosEmpleado(): HasMany
    {
        return $this->hasMany(DocumentoEmpleado::class, 'N_Empleado', 'N_Empleado');
    }

    public function documentosLaborales(): HasMany
    {
        return $this->hasMany(DocumentoLaboral::class, 'N_Empleado', 'N_Empleado');
    }

    public function asignacionesProductos(): HasMany
    {
        return $this->hasMany(AsignacionProducto::class, 'N_Empleado', 'N_Empleado');
    }

    public function pagosMensuales(): HasMany
    {
        return $this->hasMany(PagoMensual::class, 'N_Empleado', 'N_Empleado');
    }

    public function vacaciones(): HasMany
    {
        return $this->hasMany(Vacacion::class, 'N_Empleado', 'N_Empleado');
    }

    public function incapacidades(): HasMany
    {
        return $this->hasMany(Incapacidad::class, 'N_Empleado', 'N_Empleado');
    }
}
