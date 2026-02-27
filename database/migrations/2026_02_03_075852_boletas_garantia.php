<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boletas_garantia', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('agente_servicio_id')->constrained('agentes_servicio');
            $table->foreignId('entidad_id')->constrained('entidades');
            $table->foreignId('proyecto_id')->constrained('proyectos');

            $table->foreignId('banco_egreso_id')->constrained('bancos');

            $table->string('nro_boleta', 80);

            // SERIEDAD | CUMPLIMIENTO
            $table->string('tipo', 30);

            $table->string('moneda', 3); // BOB | USD

            $table->decimal('retencion', 14, 2);
            //$table->decimal('comision', 14, 2)->default(0);
            //$table->decimal('total', 14, 2);

            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observacion')->nullable();
            $table->string('foto_comprobante')->nullable();

            // abierta | devuelta
            $table->string('estado', 20)->default('abierta');
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index(['empresa_id', 'estado', 'active']);
            $table->unique(['empresa_id', 'nro_boleta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletas_garantia');
    }
};
