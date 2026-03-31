<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestamos_herramientas', function (Blueprint $table) {
            $table->string('nro_prestamo', 50)->nullable()->after('herramienta_id');
        });
    }

    public function down(): void
    {
        Schema::table('prestamos_herramientas', function (Blueprint $table) {
            $table->dropColumn('nro_prestamo');
        });
    }
};
