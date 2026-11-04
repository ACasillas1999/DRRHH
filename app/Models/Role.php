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
        [$section, $item] = array_pad(explode('.', $key, 2), 2, null);
        $perms = $this->permissions ?? [];

        return $item === null ? !empty($perms[$section]) : !empty($perms[$section][$item]);
    }

    /** Lista plana de permisos activos, p.ej. ["modules.empleados", ...]. */
    public function permisosActivos(): array
    {
        $activos = [];
        foreach ($this->permissions ?? [] as $section => $items) {
            foreach ($items as $key => $on) {
                if ($on) {
                    $activos[] = "{$section}.{$key}";
                }
            }
        }

        return $activos;
    }

    /** Todos los permisos del catálogo (config/permissions.php) en false. */
    public static function defaultPermissions(): array
    {
        return collect(config('permissions'))
            ->map(fn ($items) => array_fill_keys(array_keys($items), false))
            ->all();
    }
}
