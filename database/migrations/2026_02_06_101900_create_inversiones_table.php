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

            $table->string('codigo', 30);
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento')->nullable();

            $table->string('nombre_completo', 150);

            $table->foreignId('responsable_id')->constrained('users');

            $table->string('moneda', 3)->default('BOB'); // BOB | USD
            $table->string('tipo', 15)->default('PRIVADO'); // PRIVADO | BANCO

            $table->foreignId('banco_id')->nullable()->constrained('bancos')->nullOnDelete();

            $table->decimal('capital_actual', 14, 2)->default(0);
            $table->decimal('porcentaje_utilidad', 8, 4)->default(0);

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
