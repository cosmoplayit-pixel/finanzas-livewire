<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('entidades', function (Blueprint $table) {
            if (!Schema::hasColumn('entidades', 'empresa_id')) {
                $table
                    ->foreignId('empresa_id')
                    ->after('id')
                    ->constrained('empresas')
                    ->cascadeOnDelete();

                $table->index('empresa_id');
            }

            // ✅ únicos por empresa (permiten repetir nombre/sigla entre empresas)
            $table->unique(['empresa_id', 'nombre']);
            $table->unique(['empresa_id', 'sigla']);
        });
    }

    public function down(): void
    {
        Schema::table('entidades', function (Blueprint $table) {
            // quitar únicos compuestos
            $table->dropUnique(['empresa_id', 'nombre']);
            $table->dropUnique(['empresa_id', 'sigla']);

            if (Schema::hasColumn('entidades', 'empresa_id')) {
                $table->dropConstrainedForeignId('empresa_id');
            }
        });
    }
};
