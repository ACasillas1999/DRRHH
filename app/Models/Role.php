<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name', 'slug', 'description', 'color', 'is_system', 'permissions'];

    protected $casts = [
        'permissions' => 'array',
        'is_system'   => 'boolean',
    ];

    /** Usuarios con este rol (ligados por usuarios.Rol = roles.name). */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'Rol', 'name');
    }

    /** Permisos del rol (pivote permission_role). */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * @param  string  $key  "modules.empleados" o "actions.incapacidades_subir"
     */
    public function hasPermission(string $key): bool
    {
        // $this->permisos (propiedad, no método) consulta la pivote una sola vez y la reutiliza:
        // aunque la página tenga muchos @can, es 1 consulta por petición
        if (!str_contains($key, '.')) {
            return $this->permisos->contains('seccion', $key);
        }

        return $this->permisos->contains('clave', $key);
    }

    /** Lista plana de permisos activos, p.ej. ["modules.empleados", ...]. */
    public function permisosActivos(): array
    {
        return $this->permisos->pluck('clave')->all();
    }

    /** Todos los permisos del catálogo (config/permissions.php) en false. */
    public static function defaultPermissions(): array
    {
        return collect(config('permissions'))
            ->map(fn ($items) => array_fill_keys(array_keys($items), false))
            ->all();
    }
}
