<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();

            // Relación con entidad (sí existe previamente)
            $table->foreignId('entidad_id')->constrained('entidades')->cascadeOnDelete();

            // =========================
            // Datos del proyecto
            // =========================
            $table->string('nombre', 255);
            $table->string('codigo', 100)->nullable();
            $table->decimal('monto', 12, 2)->default(0);

            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->decimal('retencion', 5, 2)->default(0);

            // Estado
            $table->boolean('active')->default(true);

            // =========================
            // Multi-empresa (UNIFICADO)
            // =========================
            // Sin FK por orden de migraciones (evita errno 150)
            $table->unsignedBigInteger('empresa_id')->nullable()->index();

            $table->timestamps();

            // Índices
            $table->index('active');
            $table->index('entidad_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
