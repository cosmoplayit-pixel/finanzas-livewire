<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agentes_servicio', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->string('nombre', 150);
            $table->string('ci', 20);
            $table->string('nro_celular', 30)->nullable();

            // Saldos acumulados (a rendir)
            $table->decimal('saldo_bob', 14, 2)->default(0);
            $table->decimal('saldo_usd', 14, 2)->default(0);

            $table->boolean('active')->default(true);
            $table->timestamps();

            // Integridad / performance
            $table->unique(['empresa_id', 'ci']);
            $table->index(['empresa_id', 'active', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agentes_servicio');
    }
};
