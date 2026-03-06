<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla unificada: absorbe agente_presupuestos + rendicion.
 * Una rendición ES el presupuesto. Sus movimientos (COMPRA/DEVOLUCION)
 * viven en rendicion_movimientos (tabla hija).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendiciones', function (Blueprint $table) {
            $table->id();

            // Contexto organizacional
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('agente_servicio_id')->constrained('agentes_servicio')->cascadeOnDelete();

            // Banco del presupuesto (de dónde salió el dinero)
            $table->foreignId('banco_id')->constrained('bancos')->restrictOnDelete();

            // Moneda base
            $table->enum('moneda', ['BOB', 'USD']);

            // Monto original presupuestado
            $table->decimal('monto', 14, 2);

            // Nro de transacción bancaria del presupuesto
            $table->string('nro_transaccion', 50)->nullable();

            // Snapshots bancarios al momento de crear el presupuesto
            $table->decimal('saldo_banco_antes', 14, 2);
            $table->decimal('saldo_banco_despues', 14, 2);

            // Acumulados de rendición (calculados)
            $table->decimal('rendido_total', 14, 2)->default(0);
            $table->decimal('saldo_por_rendir', 14, 2)->default(0);

            // Número de rendición (autogenerado)
            $table->string('nro_rendicion', 50)->nullable();

            // Fechas
            $table->dateTime('fecha_presupuesto')->nullable();
            $table->date('fecha_cierre')->nullable();

            // Estado
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->boolean('active')->default(true);

            // Datos extra
            $table->text('observacion')->nullable();
            $table->string('foto_comprobante')->nullable();

            // Auditoría
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            // Índices
            $table->index(['empresa_id', 'agente_servicio_id'], 'ren_emp_ag_idx');
            $table->index(['empresa_id', 'agente_servicio_id', 'moneda', 'estado', 'active'], 'ren_panel_idx');
            $table->index(['empresa_id', 'estado', 'active'], 'ren_estado_idx');
            $table->index(['empresa_id', 'nro_rendicion'], 'ren_nro_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendiciones');
    }
};
