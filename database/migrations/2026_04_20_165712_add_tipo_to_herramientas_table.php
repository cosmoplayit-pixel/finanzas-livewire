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
        Schema::table('herramientas', function (Blueprint $table) {
            $table->enum('tipo', ['herramienta', 'activo', 'material'])->default('herramienta')->after('nombre');
        });

        Schema::create('herramienta_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('herramienta_id')->constrained('herramientas')->onDelete('cascade');
            $table->string('serie')->unique(); // No pueden haber series duplicadas globales o por marca? global por seguridad.
            $table->enum('estado', ['disponible', 'prestado', 'mantenimiento', 'baja'])->default('disponible');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // En prestamos_herramientas
        Schema::table('prestamos_herramientas', function (Blueprint $table) {
            $table->foreignId('serie_id')->nullable()->after('herramienta_id')->constrained('herramienta_series')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamos_herramientas', function (Blueprint $table) {
            $table->dropForeign(['serie_id']);
            $table->dropColumn('serie_id');
        });

        Schema::dropIfExists('herramienta_series');

        Schema::table('herramientas', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
