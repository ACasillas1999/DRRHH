<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de roles con permisos granulares (JSON).
     *
     * gpoascen_rh es compartida con el sistema legacy RH/, por eso NO se toca
     * la tabla usuarios: el rol se sigue guardando como texto en usuarios.Rol
     * y se liga a roles.name.
     */
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            return;
        }

        Schema::create('roles', function (Blueprint $table) {
            // Misma intercalación que usuarios: roles.name se compara contra usuarios.Rol
            $table->collation('utf8mb4_general_ci');
            $table->id();
            $table->string('name', 50)->unique();            // Debe coincidir con usuarios.Rol ("Admin", "RH"...)
            $table->string('slug', 60)->unique();
            $table->string('description')->nullable();
            $table->string('color', 7)->default('#1d4ed8');
            $table->boolean('is_system')->default(false);    // No se puede renombrar ni borrar (el legacy depende del nombre)
            $table->json('permissions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
