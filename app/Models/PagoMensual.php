<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoMensual extends Model
{
    protected $table = 'pagos_mensuales';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'N_Empleado', 'N_Empleado');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ID_Producto', 'ID');
    }
}
