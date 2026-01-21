<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();

            $table->string('numero', 100)->nullable(); // nro factura (opcional)
            $table->dateTime('fecha_emision')->nullable();
            $table->decimal('monto_facturado', 14, 2)->default(0);
            $table->decimal('retencion', 14, 2)->default(0); // ✅ Retención

            $table->text('observacion')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            // Índices
            $table->index(['proyecto_id', 'active']);
            $table->index(['fecha_emision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
