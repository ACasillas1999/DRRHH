<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Responsable extends Model
{
    protected $table = 'Responsables';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
