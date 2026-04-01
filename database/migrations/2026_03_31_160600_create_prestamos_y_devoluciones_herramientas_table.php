<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamos_herramientas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nro_prestamo', 50)->nullable();
            $table->foreignId('herramienta_id')->constrained('herramientas')->cascadeOnDelete();


            // Receptor: puede ser un agente o un nombre manual
            $table->foreignId('agente_id')->nullable()->constrained('agentes_servicio')->nullOnDelete();
            $table->string('receptor_manual', 200)->nullable();

            // Destino
            $table->foreignId('entidad_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();

            // Cantidades
            $table->unsignedInteger('cantidad_prestada');
            $table->unsignedInteger('cantidad_devuelta')->default(0);

            // Fechas
            $table->date('fecha_prestamo');
            $table->date('fecha_vencimiento')->nullable();

            // Estado y Evidencia
            $table->string('estado', 50)->default('activo'); // activo, vencido, finalizado
            $table->json('fotos_salida')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index(['empresa_id', 'estado', 'fecha_prestamo']);
            $table->index(['herramienta_id']);
        });

        Schema::create('devolucion_herramientas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestamo_id')->constrained('prestamos_herramientas')->cascadeOnDelete();

            $table->unsignedInteger('cantidad_devuelta');
            $table->date('fecha_devolucion');

            $table->string('estado_fisico', 50)->nullable(); // bueno, regular, malo
            $table->json('fotos_entrada')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index(['prestamo_id', 'fecha_devolucion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devolucion_herramientas');
        Schema::dropIfExists('prestamos_herramientas');
    }
};
