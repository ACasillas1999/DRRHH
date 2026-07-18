<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checador extends Model
{
    // Mapear a la tabla
    protected $table = 'checadores';

    // Llave primaria
    protected $primaryKey = 'id';

    // Permitir asignación masiva
    protected $guarded = [];
}
