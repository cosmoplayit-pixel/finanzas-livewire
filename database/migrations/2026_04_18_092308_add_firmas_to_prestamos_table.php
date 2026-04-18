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
        Schema::table('prestamos_herramientas', function (Blueprint $table) {
            $table->longText('firma_salida')->nullable()->after('fotos_salida');
        });

        Schema::table('devolucion_herramientas', function (Blueprint $table) {
            $table->longText('firma_entrada')->nullable()->after('fotos_entrada');
        });
    }

    public function down(): void
    {
        Schema::table('prestamos_herramientas', function (Blueprint $table) {
            $table->dropColumn('firma_salida');
        });

        Schema::table('devolucion_herramientas', function (Blueprint $table) {
            $table->dropColumn('firma_entrada');
        });
    }
};
