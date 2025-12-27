<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('entidad_id')->constrained('entidades')->cascadeOnDelete();

            $table->string('nombre', 255);
            $table->string('codigo', 100)->nullable();
            $table->decimal('monto', 12, 2)->default(0);

            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->boolean('active')->default(true);
            $table->timestamps();

            // ✅ NO unique global aquí; se hará por empresa luego
            $table->index('active');
            $table->index('entidad_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
