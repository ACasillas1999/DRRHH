<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Crea los 6 roles que ya usa usuarios.Rol, con permisos que reproducen
 * exactamente el acceso que daban los grupos role:... de routes/web.php.
 *
 * Usa firstOrCreate: si se vuelve a correr, NO pisa permisos editados
 * desde la pantalla /roles.
 *
 *   php artisan db:seed --class=RolesSeeder
 */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $rhModulos = [
            'empleados', 'altas_imss', 'razones_sociales', 'puestos', 'reportes', 'diario',
            'incidencias', 'checadores', 'reclutamiento', 'historial', 'inventario',
        ];

        $roles = [
            'Admin'     => ['Administrador',     '#1d4ed8', ['modules' => array_keys(config('permissions.modules')), 'actions' => array_keys(config('permissions.actions'))]],
            'RH'        => ['Recursos Humanos',  '#16a34a', ['modules' => $rhModulos, 'actions' => ['incapacidades_subir']]],
            'Nominas'   => ['Nóminas',           '#d97706', ['modules' => ['empleados', 'altas_imss'], 'actions' => []]],
            'Jefe'      => ['Jefe',              '#64748b', ['modules' => [], 'actions' => []]],
            'Gerente'   => ['Gerente',           '#64748b', ['modules' => [], 'actions' => []]],
            'Encargado' => ['Encargado',         '#64748b', ['modules' => [], 'actions' => []]],
        ];

        foreach ($roles as $name => [$description, $color, $grant]) {
            $permissions = Role::defaultPermissions();
            foreach ($grant as $section => $keys) {
                foreach ($keys as $key) {
                    $permissions[$section][$key] = true;
                }
            }

            Role::firstOrCreate(['name' => $name], [
                'slug'        => Str::slug($name),
                'description' => $description,
                'color'       => $color,
                'is_system'   => true,
                'permissions' => $permissions,
            ]);
        }
    }
}
