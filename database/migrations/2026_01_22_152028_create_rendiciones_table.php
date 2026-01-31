<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rendiciones', function (Blueprint $table) {
            $table->id();

            // =========================
            // Contexto organizacional
            // =========================
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table
                ->foreignId('agente_servicio_id')
                ->constrained('agentes_servicio')
                ->cascadeOnDelete();

            // =========================
            // Cabecera de rendición
            // =========================
            $table->enum('moneda', ['BOB', 'USD']);

            // Nro rendición (puede ser null mientras está "pendiente" si tú lo generas luego)
            $table->string('nro_rendicion', 50)->nullable();

            // Fecha cabecera/inicio (lo que tu código llamaba fecha_rendicion)
            $table->date('fecha_rendicion')->nullable();

            // Cierre (cuando se complete y pase a cerrada)
            $table->date('fecha_cierre')->nullable();

            // =========================
            // Totales
            // =========================
            // Total de presupuesto asignado a esta rendición (normalmente suma de agente_presupuestos vinculados)
            $table->decimal('presupuesto_total', 14, 2)->default(0);

            // Total rendido (suma de movimientos base)
            $table->decimal('rendido_total', 14, 2)->default(0);

            // Saldo restante por rendir (presupuesto_total - rendido_total)
            $table->decimal('saldo', 14, 2)->default(0);

            // =========================
            // Estado
            // =========================
            // pendiente: creada pero aún no operada / o sin cierre
            // abierto: operando (con movimientos)
            // cerrado: saldo = 0 (o regla de cierre cumplida)
            $table->enum('estado', ['pendiente', 'abierto', 'cerrado'])->default('pendiente');
            $table->boolean('active')->default(true);

            // Auditoría
            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();

            // =========================
            // Índices (nombres cortos)
            // =========================
            $table->index(['empresa_id', 'agente_servicio_id'], 'ren_emp_ag_idx');

            $table->index(
                ['empresa_id', 'agente_servicio_id', 'moneda', 'estado', 'active'],
                'ren_panel_idx',
            );

            // Para búsquedas por nro (si lo usas en UI)
            $table->index(['empresa_id', 'nro_rendicion'], 'ren_nro_idx');

            // Recomendado si quieres que no se repita nro dentro de la empresa (cuando no sea null)
            // MySQL permite múltiples NULL en UNIQUE, así que no te bloquea mientras sea null.
            $table->unique(['empresa_id', 'nro_rendicion'], 'ren_emp_nro_ux');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendiciones');
    }
};
