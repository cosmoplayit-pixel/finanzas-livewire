<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inversion_movimientos', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('inversion_id')->constrained('inversions')->cascadeOnDelete();

            $table->unsignedInteger('nro');

            $table->string('tipo', 30);

            // =========================
            // SOLO PARA BANCO
            // =========================

            $table->date('fecha'); // Capital: fecha mov | Utilidad: fecha final | Banco: fecha contable
            $table->date('fecha_pago')->nullable(); // fecha prevista / fecha pagada según tu flujo

            $table->string('descripcion')->nullable();

            // =========================
            // ✅ CONTROL DE ESTADO (PENDIENTE / PAGADO)
            // =========================
            $table->string('estado', 15)->default('PENDIENTE'); // PENDIENTE | PAGADO | ANULADO
            $table->timestamp('pagado_en')->nullable(); // se llena cuando se confirma el débito real

            // =========================
            // BANCO: desglose (opcional)
            // =========================
            $table->decimal('monto_total', 14, 4)->nullable(); // total pagado (moneda base)
            $table->decimal('monto_interes', 14, 4)->nullable();
            $table->decimal('monto_mora', 14, 4)->nullable();
            $table->decimal('monto_comision', 14, 4)->nullable();
            $table->decimal('monto_seguro', 14, 4)->nullable();

            // Capital (+ ingreso / - devolución) o (BANCO: capital pagado, guardamos en monto_capital positivo)
            $table->decimal('monto_capital', 14, 4)->nullable();

            // Pago utilidad (monto A PAGAR calculado)
            $table->decimal('monto_utilidad', 14, 4)->nullable();

            // % utilidad calculado (monto_mes / capital_total * 100)
            $table->decimal('porcentaje_utilidad', 8, 4)->nullable();

            // Auditoría pago utilidad por días
            $table->date('utilidad_fecha_inicio')->nullable(); // inicio auto usado en cálculo
            $table->unsignedTinyInteger('utilidad_dias')->nullable(); // días usados (15, 27, 30, etc.)
            $table->decimal('utilidad_monto_mes', 14, 4)->nullable(); // monto mes manual digitado

            // Moneda/TC (si banco moneda != inversión)
            $table->string('moneda_banco', 3)->nullable(); // ej: BOB, USD
            $table->decimal('tipo_cambio', 14, 4)->nullable();

            // Banco + comprobantes
            $table->foreignId('banco_id')->nullable()->constrained('bancos')->nullOnDelete();

            $table->string('comprobante', 255)->nullable();
            $table->string('comprobante_imagen_path', 255)->nullable();

            $table->timestamps();

            $table->index(['inversion_id', 'fecha']);
            $table->index(['tipo']);
            $table->index(['banco_id']);
            $table->index(['fecha_pago']);
            $table->index(['estado']);

            $table->unique(['inversion_id', 'nro']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inversion_movimientos');
    }
};
