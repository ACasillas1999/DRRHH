<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permisos en tablas en lugar del JSON roles.permissions.
     *
     * permissions:     catálogo (se llena desde config/permissions.php).
     * permission_role: una fila = el rol tiene ese permiso.
     * La columna roles.permissions se queda de respaldo hasta que esto esté estable.
     */
    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->collation('utf8mb4_general_ci');
                $table->id();
                $table->string('clave', 100)->unique();      // "modules.empleados", "actions.incidencias_editar"
                $table->string('seccion', 30);               // "modules" o "actions"
                $table->string('label');
                $table->string('descripcion')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->collation('utf8mb4_general_ci');
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->primary(['role_id', 'permission_id']);  // Un rol no puede tener el mismo permiso dos veces
            });
        }
    }

    public function down(): void
    {
        // Primero la pivote: depende de las otras dos
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
    }
};
