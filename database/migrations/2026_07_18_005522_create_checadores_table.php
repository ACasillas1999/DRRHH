<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checadores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ip', 50);
            $table->integer('puerto')->default(80);
            $table->string('usuario', 50)->default('admin');
            $table->string('password', 100)->default('cga302630');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Seed con los 19 checadores predeterminados de la empresa
        $devices = [
            ['nombre' => 'Aiesa', 'ip' => '192.168.40.199'],
            ['nombre' => 'CODI', 'ip' => '192.168.150.200'],
            ['nombre' => 'Calzada', 'ip' => '192.168.91.201'],
            ['nombre' => 'Constituyentes', 'ip' => '192.168.90.200'],
            ['nombre' => 'Corporativo', 'ip' => '192.168.100.200'],
            ['nombre' => 'Deasa', 'ip' => '192.168.20.244'],
            ['nombre' => 'Dimegsa', 'ip' => '192.168.10.200'],
            ['nombre' => 'España', 'ip' => '192.168.10.201'],
            ['nombre' => 'Federalismo', 'ip' => '192.168.100.201'],
            ['nombre' => 'Fesa', 'ip' => '192.168.50.201'],
            ['nombre' => 'Gabsa', 'ip' => '192.168.1.201'],
            ['nombre' => 'Iluminacion', 'ip' => '192.168.2.201'],
            ['nombre' => 'Ovalo', 'ip' => '192.168.60.203'],
            ['nombre' => 'Queretaro', 'ip' => '192.168.140.200'],
            ['nombre' => 'Segsa', 'ip' => '192.168.30.200'],
            ['nombre' => 'Taller', 'ip' => '192.168.130.200'],
            ['nombre' => 'Tapatia', 'ip' => '192.168.70.200'],
            ['nombre' => 'Vallarta', 'ip' => '192.168.120.200'],
            ['nombre' => 'VentasCampo', 'ip' => '192.168.2.200'],
        ];

        foreach ($devices as $dev) {
            DB::table('checadores')->insert([
                'nombre' => $dev['nombre'],
                'ip' => $dev['ip'],
                'puerto' => 80,
                'usuario' => 'admin',
                'password' => 'cga302630',
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checadores');
    }
};
