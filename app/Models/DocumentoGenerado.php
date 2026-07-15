<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoGenerado extends Model
{
    protected $table = 'documentos_generados';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'N_Empleado', 'N_Empleado');
    }
}
