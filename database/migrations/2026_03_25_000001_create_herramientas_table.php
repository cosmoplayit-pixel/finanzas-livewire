<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('herramientas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->string('codigo', 50)->nullable();
            $table->string('nombre', 200);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->text('descripcion')->nullable();

            // Estado físico: bueno, regular, malo, baja
            $table->string('estado_fisico', 30)->default('bueno');

            // Unidad de medida: unidad, juego, caja, etc.
            $table->string('unidad', 50)->nullable();

            // Stock
            $table->unsignedInteger('stock_total')->default(0);
            $table->unsignedInteger('stock_disponible')->default(0);
            $table->unsignedInteger('stock_prestado')->default(0);

            // Precios
            $table->decimal('precio_unitario', 14, 2)->default(0);
            $table->decimal('precio_total', 14, 2)->default(0);

            // Imagen
            $table->string('imagen', 500)->nullable();

            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['empresa_id', 'active', 'nombre']);
            $table->index(['empresa_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('herramientas');
    }
};
