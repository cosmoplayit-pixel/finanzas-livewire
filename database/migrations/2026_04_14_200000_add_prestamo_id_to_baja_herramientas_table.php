<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('baja_herramientas', function (Blueprint $table) {
            $table->foreignId('prestamo_id')
                ->nullable()
                ->after('herramienta_id')
                ->constrained('prestamos_herramientas')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('baja_herramientas', function (Blueprint $table) {
            $table->dropForeign(['prestamo_id']);
            $table->dropColumn('prestamo_id');
        });
    }
};
