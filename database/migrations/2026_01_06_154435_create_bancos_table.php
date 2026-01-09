<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bancos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->string('nombre', 150);
            $table->string('titular', 150)->nullable();
            $table->string('numero_cuenta', 50);
            $table->enum('moneda', ['BOB', 'USD']);
            $table->enum('tipo_cuenta', ['AHORRO', 'CORRIENTE'])->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Reglas de integridad
            $table->unique(['empresa_id', 'numero_cuenta']);
            $table->index(['empresa_id', 'nombre', 'moneda', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bancos');
    }
};
