<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Describe el rol (para UI)
            $table->string('description')->nullable()->after('name');

            // Marca roles base del sistema (Administrador, Empresa_Manager, Empresa_Visualizador)
            $table->boolean('is_system')->default(false)->after('description');

            // Permite activar / desactivar roles personalizados
            $table->boolean('active')->default(true)->after('is_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_system', 'active']);
        });
    }
};
