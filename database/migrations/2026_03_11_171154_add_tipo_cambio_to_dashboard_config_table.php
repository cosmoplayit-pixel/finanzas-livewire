<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_config', function (Blueprint $table) {
            $table->decimal('tipo_cambio', 15, 2)->default(1.00)->after('impuestos_nacionales_usd');
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_config', function (Blueprint $table) {
            $table->dropColumn('tipo_cambio');
        });
    }
};
