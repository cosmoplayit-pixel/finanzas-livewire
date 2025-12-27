<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            if (!Schema::hasColumn('proyectos', 'empresa_id')) {
                $table
                    ->foreignId('empresa_id')
                    ->after('id')
                    ->constrained('empresas')
                    ->cascadeOnDelete();

                $table->index('empresa_id');
            }

            // ✅ si usas código, que sea único por empresa (no global)
            $table->unique(['empresa_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropUnique(['empresa_id', 'codigo']);

            if (Schema::hasColumn('proyectos', 'empresa_id')) {
                $table->dropConstrainedForeignId('empresa_id');
            }
        });
    }
};
