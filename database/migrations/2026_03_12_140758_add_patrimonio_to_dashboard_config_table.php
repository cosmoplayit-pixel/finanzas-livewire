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
        Schema::table('dashboard_config', function (Blueprint $table) {
            $table->decimal('patrimonio_herramientas_bob', 15, 2)->default(0)->after('tipo_cambio');
            $table->decimal('patrimonio_herramientas_usd', 15, 2)->default(0)->after('patrimonio_herramientas_bob');
            $table->decimal('patrimonio_materiales_bob', 15, 2)->default(0)->after('patrimonio_herramientas_usd');
            $table->decimal('patrimonio_materiales_usd', 15, 2)->default(0)->after('patrimonio_materiales_bob');
            $table->decimal('patrimonio_mobiliario_bob', 15, 2)->default(0)->after('patrimonio_materiales_usd');
            $table->decimal('patrimonio_mobiliario_usd', 15, 2)->default(0)->after('patrimonio_mobiliario_bob');
            $table->decimal('patrimonio_vehiculos_bob', 15, 2)->default(0)->after('patrimonio_mobiliario_usd');
            $table->decimal('patrimonio_vehiculos_usd', 15, 2)->default(0)->after('patrimonio_vehiculos_bob');
            $table->decimal('patrimonio_inmuebles_bob', 15, 2)->default(0)->after('patrimonio_vehiculos_usd');
            $table->decimal('patrimonio_inmuebles_usd', 15, 2)->default(0)->after('patrimonio_inmuebles_bob');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboard_config', function (Blueprint $table) {
            $table->dropColumn([
                'patrimonio_herramientas_bob', 'patrimonio_herramientas_usd',
                'patrimonio_materiales_bob', 'patrimonio_materiales_usd',
                'patrimonio_mobiliario_bob', 'patrimonio_mobiliario_usd',
                'patrimonio_vehiculos_bob', 'patrimonio_vehiculos_usd',
                'patrimonio_inmuebles_bob', 'patrimonio_inmuebles_usd',
            ]);
        });
    }
};
