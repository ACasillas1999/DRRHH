<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checadores', function (Blueprint $table) {
            $table->boolean('push_habilitado')->default(false)->after('activo');
            $table->timestamp('push_ultimo_evento_at')->nullable()->after('push_habilitado');
        });
    }

    public function down(): void
    {
        Schema::table('checadores', function (Blueprint $table) {
            $table->dropColumn(['push_habilitado', 'push_ultimo_evento_at']);
        });
    }
};
