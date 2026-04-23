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
        Schema::table('herramienta_series', function (Blueprint $table) {
            $table->foreignId('baja_id')->nullable()->after('estado')->constrained('baja_herramientas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('herramienta_series', function (Blueprint $table) {
            $table->dropForeign(['baja_id']);
            $table->dropColumn('baja_id');
        });
    }
};
