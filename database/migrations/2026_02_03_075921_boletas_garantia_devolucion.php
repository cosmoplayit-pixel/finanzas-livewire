<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boleta_garantia_devoluciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('boleta_garantia_id')
                ->constrained('boletas_garantia')
                ->cascadeOnDelete();

            // Banco destino (ingreso)
            $table->foreignId('banco_id')->constrained('bancos');

            $table->dateTime('fecha_devolucion');

            $table->decimal('monto', 14, 2);

            $table->string('nro_transaccion', 100)->nullable();
            $table->text('observacion')->nullable();
            $table->string('foto_comprobante')->nullable();

            // Auditoría del banco destino
            $table->decimal('saldo_banco_antes', 14, 2);
            $table->decimal('saldo_banco_despues', 14, 2);

            $table->timestamps();

            $table->index(['boleta_garantia_id']);
            $table->index(['banco_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boleta_garantia_devoluciones');
    }
};
