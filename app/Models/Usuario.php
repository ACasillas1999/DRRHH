<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = ['Password'];
}
