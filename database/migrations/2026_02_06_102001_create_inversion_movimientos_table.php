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
            $table->date('fecha'); // Capital: fecha mov | Utilidad: fecha final
            $table->date('fecha_pago')->nullable();

            $table->string('descripcion')->nullable();

            // Capital (+ ingreso / - devolución)
            $table->decimal('monto_capital', 14, 2)->nullable();

            // Pago utilidad (monto A PAGAR calculado)
            $table->decimal('monto_utilidad', 14, 2)->nullable();

            // % utilidad calculado (monto_mes / capital_total * 100)
            $table->decimal('porcentaje_utilidad', 8, 2)->nullable();

            // Auditoría pago utilidad por días
            $table->date('utilidad_fecha_inicio')->nullable(); // inicio auto usado en cálculo
            $table->unsignedTinyInteger('utilidad_dias')->nullable(); // días usados (15, 27, 30, etc.)
            $table->decimal('utilidad_monto_mes', 14, 2)->nullable(); // monto mes manual digitado

            // Moneda/TC (si banco moneda != inversión)
            $table->string('moneda_banco', 3)->nullable(); // ej: BOB, USD
            $table->decimal('tipo_cambio', 14, 2)->nullable(); // si quieres 2 decimales cambia a (14,2)

            // Banco + comprobantes
            $table->foreignId('banco_id')->nullable()->constrained('bancos')->nullOnDelete();
            $table->string('comprobante', 255)->nullable();
            $table->string('comprobante_imagen_path', 255)->nullable();

            $table->timestamps();

            $table->index(['inversion_id', 'fecha']);
            $table->index(['tipo']);
            $table->index(['banco_id']);
            $table->index(['fecha_pago']);
            $table->unique(['inversion_id', 'nro']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inversion_movimientos');
    }
};
