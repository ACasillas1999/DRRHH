<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RazonSocial extends Model
{
    protected $table = 'razones_sociales';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
}
