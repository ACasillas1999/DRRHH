<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Crea los 6 roles que ya usa usuarios.Rol, con permisos que reproducen
 * exactamente el acceso que daban los grupos role:... de routes/web.php.
 *
 * Llama a PermissionsSeeder y escribe la pivote permission_role solo en roles
 * recién creados. Usa firstOrCreate: si se vuelve a correr, NO pisa permisos editados
 * desde la pantalla /roles.
 *
 *   php artisan db:seed --class=RolesSeeder
 */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Primero el catálogo: la pivote necesita que existan los ids de permissions
        $this->call(PermissionsSeeder::class);


        $rhModulos = [
            'empleados', 'altas_imss', 'razones_sociales', 'puestos', 'reportes', 'diario',
            'incidencias', 'checadores', 'reclutamiento', 'historial', 'inventario',
        ];

        $roles = [
            'Admin'     => ['Administrador',     '#1d4ed8', ['modules' => array_keys(config('permissions.modules')), 'actions' => array_keys(config('permissions.actions'))]],
            'RH'        => ['Recursos Humanos',  '#16a34a', ['modules' => $rhModulos, 'actions' => ['incapacidades_subir', 'incidencias_editar']]],
            'Nominas'   => ['Nóminas',           '#d97706', ['modules' => ['empleados', 'altas_imss'], 'actions' => []]],
            'Jefe'      => ['Jefe',              '#64748b', ['modules' => [], 'actions' => []]],
            'Gerente'   => ['Gerente',           '#64748b', ['modules' => [], 'actions' => []]],
            'Encargado' => ['Encargado',         '#64748b', ['modules' => [], 'actions' => []]],
        ];

        foreach ($roles as $name => [$description, $color, $grant]) {
            $permissions = Role::defaultPermissions();
            $claves      = [];   // "modules.empleados", ... para la pivote
            foreach ($grant as $section => $keys) {
                foreach ($keys as $key) {
                    $permissions[$section][$key] = true;
                    $claves[] = "{$section}.{$key}";
                }
            }

            $role = Role::firstOrCreate(['name' => $name], [
                'slug'        => Str::slug($name),
                'description' => $description,
                'color'       => $color,
                'is_system'   => true,
                'permissions' => $permissions,
            ]);
             // Solo a roles recién creados: si ya existía, se respetan los permisos editados en /roles
            if ($role->wasRecentlyCreated) {
                $role->permisos()->sync(Permission::whereIn('clave', $claves)->pluck('id'));
            }
        }
    }
}
