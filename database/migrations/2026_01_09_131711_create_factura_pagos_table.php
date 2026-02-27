<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('factura_pagos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();

            // Banco destino (tu tabla 'bancos'). Nullable si efectivo.
            $table->foreignId('banco_id')->nullable()->constrained('bancos')->nullOnDelete();

            $table->dateTime('fecha_pago')->nullable();

            // normal | retencion
            $table->enum('tipo', ['normal', 'retencion']);

            $table->decimal('monto', 14, 2)->default(0);

            // transferencia|deposito|cheque|efectivo|tarjeta|qr|otro
            $table->string('metodo_pago', 30)->nullable();

            $table->string('nro_operacion', 80)->nullable();
            $table->string('comprobante_path', 255)->nullable();
            $table->string('foto_comprobante')->nullable();
            $table->text('observacion')->nullable();

            // Snapshot destino (auditoría histórica)
            $table->string('destino_banco_nombre_snapshot', 150)->nullable();
            $table->string('destino_numero_cuenta_snapshot', 50)->nullable();
            $table->string('destino_moneda_snapshot', 3)->nullable();
            $table->string('destino_titular_snapshot', 150)->nullable();
            $table->string('destino_tipo_cuenta_snapshot', 20)->nullable();

            $table->timestamps();

            // Índices
            $table->index(['factura_id', 'tipo']);
            $table->index(['banco_id']);
            $table->index(['fecha_pago']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_pagos');
    }
};
