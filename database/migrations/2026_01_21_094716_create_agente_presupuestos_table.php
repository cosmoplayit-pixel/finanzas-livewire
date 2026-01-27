<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agente_presupuestos', function (Blueprint $table) {
            $table->id();

            // Contexto organizacional
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table
                ->foreignId('agente_servicio_id')
                ->constrained('agentes_servicio')
                ->cascadeOnDelete();
            $table->foreignId('banco_id')->constrained('bancos')->restrictOnDelete();

            // Enlace a rendición (nullable)
            // IMPORTANTE: definimos la columna igual (unsignedBigInteger) que un $table->id()

            $table->unsignedBigInteger('rendicion_id')->nullable();
            $table->unique('rendicion_id', 'ap_rendicion_unique'); // permite múltiples NULL, pero evita duplicar el mismo id

            // Datos del presupuesto
            $table->enum('moneda', ['BOB', 'USD']);
            $table->decimal('monto', 14, 2);

            $table->dateTime('fecha_presupuesto');
            $table->string('nro_transaccion', 50);

            // Snapshots bancarios
            $table->decimal('saldo_banco_antes', 14, 2);
            $table->decimal('saldo_banco_despues', 14, 2);

            // Rendición (acumulados)
            $table->decimal('rendido_total', 14, 2)->default(0);
            $table->decimal('saldo_por_rendir', 14, 2)->default(0);

            // Estado
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->boolean('active')->default(true);

            $table->text('observacion')->nullable();

            // Auditoría
            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();

            // Índices
            $table->index(['empresa_id', 'agente_servicio_id']);
            $table->index(['banco_id']);
            $table->index(['estado', 'active']);
            $table->index(
                ['empresa_id', 'agente_servicio_id', 'moneda', 'estado', 'active'],
                'ap_panel_idx',
            );
            $table->index(['rendicion_id'], 'ap_rendicion_idx');
        });

        // ✅ FK solo si existe la tabla rendiciones (evita error 150 por orden)
        if (Schema::hasTable('rendiciones')) {
            Schema::table('agente_presupuestos', function (Blueprint $table) {
                $table
                    ->foreign('rendicion_id')
                    ->references('id')
                    ->on('rendiciones')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Para bajar limpio
        if (Schema::hasTable('agente_presupuestos')) {
            Schema::table('agente_presupuestos', function (Blueprint $table) {
                // Si existe FK, la quitamos
                try {
                    $table->dropForeign(['rendicion_id']);
                } catch (\Throwable $e) {
                    // silencioso: si no existía FK por el condicional, no pasa nada
                }
            });
        }

        Schema::dropIfExists('agente_presupuestos');
    }
};
