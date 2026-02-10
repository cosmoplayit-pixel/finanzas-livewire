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

            // ✅ FK correcta: inversion_id BIGINT UNSIGNED -> inversions.id BIGINT UNSIGNED
            $table->foreignId('inversion_id')->constrained('inversions')->cascadeOnDelete();

            $table->unsignedInteger('nro');

            $table->string('tipo', 30);
            $table->date('fecha');
            $table->date('fecha_pago')->nullable();

            $table->string('descripcion')->nullable();

            // Capital (+ ingreso / - devolución)
            $table->decimal('monto_capital', 14, 2)->nullable();

            // Pago utilidad (sale de banco)
            $table->decimal('monto_utilidad', 14, 2)->nullable();

            $table->decimal('porcentaje_utilidad', 8, 4)->nullable();

            $table->foreignId('banco_id')->nullable()->constrained('bancos')->nullOnDelete();

            $table->string('comprobante', 255)->nullable();

            $table->timestamps();

            $table->index(['inversion_id', 'fecha']);
            $table->index(['tipo']);
            $table->unique(['inversion_id', 'nro']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inversion_movimientos');
    }
};
