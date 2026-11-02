<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'usuarios';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = ['Password'];

    public function getAuthPassword(): string
    {
        return $this->Password;
    }

    public function getAuthPasswordName(): string
    {
        return 'Password';
    }

    /** Rol del usuario: usuarios.Rol guarda el nombre (no un id) porque el legacy lo lee así. */
    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'Rol', 'name');
    }

    public function hasPermission(string $key): bool
    {
        return (bool) $this->roleModel?->hasPermission($key);
    }
}
