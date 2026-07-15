<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoImss extends Model
{
    protected $table = 'documentos_imss';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'n_empleado', 'N_Empleado');
    }
}
