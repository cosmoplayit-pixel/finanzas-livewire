<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferencias_bancarias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('banco_origen_id')->constrained('bancos');
            $table->foreignId('banco_destino_id')->constrained('bancos');

            $table->enum('moneda_origen', ['BOB', 'USD']);
            $table->enum('moneda_destino', ['BOB', 'USD']);

            // Monto debitado del banco origen (en su moneda)
            $table->decimal('monto_origen', 14, 2);
            // Monto acreditado al banco destino (en su moneda)
            $table->decimal('monto_destino', 14, 2);
            // Tipo de cambio aplicado (null si misma moneda)
            $table->decimal('tipo_cambio', 14, 6)->nullable();

            // Snapshots de saldos para auditoría
            $table->decimal('saldo_origen_antes', 14, 2);
            $table->decimal('saldo_origen_despues', 14, 2);
            $table->decimal('saldo_destino_antes', 14, 2);
            $table->decimal('saldo_destino_despues', 14, 2);

            $table->string('nro_transaccion', 60)->nullable();
            $table->datetime('fecha');
            $table->text('observacion')->nullable();
            $table->string('foto_comprobante')->nullable();

            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();

            $table->index(['empresa_id', 'fecha']);
            $table->index('banco_origen_id');
            $table->index('banco_destino_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferencias_bancarias');
    }
};
