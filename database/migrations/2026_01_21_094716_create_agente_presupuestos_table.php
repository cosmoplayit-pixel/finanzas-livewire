<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agente_presupuestos', function (Blueprint $table) {
            $table->id();

            // =========================
            // Contexto organizacional
            // =========================
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table
                ->foreignId('agente_servicio_id')
                ->constrained('agentes_servicio')
                ->cascadeOnDelete();

            // Banco origen del presupuesto
            $table->foreignId('banco_id')->constrained('bancos')->restrictOnDelete();

            // =========================
            // Datos del presupuesto
            // =========================
            $table->enum('moneda', ['BOB', 'USD']);
            $table->decimal('monto', 14, 2);

            $table->datetime('fecha_presupuesto');
            $table->string('nro_transaccion', 50);

            // =========================
            // Snapshots bancarios
            // =========================
            $table->decimal('saldo_banco_antes', 14, 2);
            $table->decimal('saldo_banco_despues', 14, 2);

            // =========================
            // Rendición (acumulados)
            // =========================
            $table->decimal('rendido_total', 14, 2)->default(0);
            $table->decimal('saldo_por_rendir', 14, 2)->default(0);

            // =========================
            // Estado
            // =========================
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->boolean('active')->default(true);

            $table->text('observacion')->nullable();

            // Auditoría
            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();

            // =========================
            // Índices
            // =========================
            $table->index(['empresa_id', 'agente_servicio_id']);
            $table->index(['banco_id']);
            $table->index(['estado', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agente_presupuestos');
    }
};
