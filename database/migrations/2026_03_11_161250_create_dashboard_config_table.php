<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->unique()->nullable();
            $table->decimal('impuestos_nacionales_bob', 15, 2)->default(0);
            $table->decimal('impuestos_nacionales_usd', 15, 2)->default(0);
            $table->decimal('tipo_cambio', 15, 2)->default(1.00);
            $table->decimal('patrimonio_herramientas_bob', 15, 2)->default(0);
            $table->decimal('patrimonio_herramientas_usd', 15, 2)->default(0);
            $table->decimal('patrimonio_materiales_bob', 15, 2)->default(0);
            $table->decimal('patrimonio_materiales_usd', 15, 2)->default(0);
            $table->decimal('patrimonio_mobiliario_bob', 15, 2)->default(0);
            $table->decimal('patrimonio_mobiliario_usd', 15, 2)->default(0);
            $table->decimal('patrimonio_vehiculos_bob', 15, 2)->default(0);
            $table->decimal('patrimonio_vehiculos_usd', 15, 2)->default(0);
            $table->decimal('patrimonio_inmuebles_bob', 15, 2)->default(0);
            $table->decimal('patrimonio_inmuebles_usd', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_config');
    }
};
