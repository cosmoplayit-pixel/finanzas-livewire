<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rendicion_movimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('rendicion_id')->constrained('rendiciones')->cascadeOnDelete();

            // COMPRA o DEVOLUCION
            $table->enum('tipo', ['COMPRA', 'DEVOLUCION']);

            // Fecha del movimiento
            $table->date('fecha');

            // Compra (opcionales)
            $table->foreignId('entidad_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();

            // Comprobante
            $table->enum('tipo_comprobante', ['FACTURA', 'RECIBO', 'TRANSFERENCIA'])->nullable();
            $table->string('nro_comprobante', 60)->nullable();

            // Para devolución bancaria
            $table->foreignId('banco_id')->nullable()->constrained('bancos')->restrictOnDelete();
            $table->string('nro_transaccion', 60)->nullable();

            // Moneda del movimiento (puede ser distinta a la base)
            $table->enum('moneda', ['BOB', 'USD']);

            // Tipo de cambio cuando moneda != rendicion.moneda
            $table->decimal('tipo_cambio', 14, 6)->nullable();

            // Montos
            $table->decimal('monto', 14, 2); // en moneda del movimiento
            $table->decimal('monto_base', 14, 2); // convertido a moneda base de la rendición

            // Adjuntos
            $table->string('foto_path')->nullable();

            $table->text('observacion')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Índices con nombre corto (evita error 1059)
            $table->index(['empresa_id', 'rendicion_id'], 'rm_rend_idx');
            $table->index(['empresa_id', 'rendicion_id', 'tipo'], 'rm_tipo_idx');
            $table->index(['empresa_id', 'fecha'], 'rm_fecha_idx');
            $table->index(['empresa_id', 'moneda', 'active'], 'rm_mon_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendicion_movimientos');
    }
};
