<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inversions', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->string('codigo', 150);
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento')->nullable();

            $table->string('nombre_completo', 150);

            $table->foreignId('responsable_id')->constrained('users');

            $table->string('moneda', 3)->default('BOB'); // BOB | USD
            $table->string('tipo', 15)->default('PRIVADO'); // PRIVADO | BANCO

            $table->foreignId('banco_id')->nullable()->constrained('bancos')->nullOnDelete();

            $table->decimal('capital_actual', 14, 4)->default(0);
            $table->decimal('porcentaje_utilidad', 8, 4)->default(0);

            // =========================
            // CAMPOS SOLO PARA BANCO
            // =========================
            $table->decimal('tasa_anual', 8, 4)->nullable(); // 0.2450 = 24.50% anual
            $table->unsignedInteger('plazo_meses')->nullable(); // 12, 24, 36...
            $table->unsignedTinyInteger('dia_pago')->nullable(); // 1..28
            $table->string('sistema', 20)->nullable(); // FRANCESA | ALEMANA | (opcional)

            $table->string('comprobante', 255)->nullable();

            $table->date('hasta_fecha')->nullable();

            $table->string('estado', 15)->default('ACTIVA'); // ACTIVA | CERRADA

            $table->timestamps();

            $table->unique(['empresa_id', 'codigo']);
            $table->index(['empresa_id', 'estado']);
            $table->index(['empresa_id', 'fecha_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inversions');
    }
};
