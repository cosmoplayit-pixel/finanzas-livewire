<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 255);
            $table->string('nit', 50)->nullable();
            $table->string('email', 255)->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            // Únicos si quieres:
            $table->unique('nombre');
            $table->unique('nit');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
