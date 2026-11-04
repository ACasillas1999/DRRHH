<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Llena la tabla permissions desde config/permissions.php.
 * Usa updateOrCreate por clave: se puede volver a correr sin duplicar.
 */
class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('permissions') as $seccion => $items) {
            foreach ($items as $key => $info) {
                Permission::updateOrCreate(
                    ['clave' => "{$seccion}.{$key}"],
                    ['seccion' => $seccion, 'label' => $info['label'], 'descripcion' => $info['desc'] ?? null]
                );
            }
        }
    }
}
