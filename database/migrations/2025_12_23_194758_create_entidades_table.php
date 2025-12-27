<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entidades', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 255);
            $table->string('sigla', 50)->nullable();

            $table->string('email', 255)->nullable();
            $table->string('telefono', 60)->nullable();
            $table->text('direccion')->nullable();
            $table->text('observaciones')->nullable();

            $table->boolean('active')->default(true);

            $table->timestamps();

            // ✅ NO unique global aquí (porque se repetirá por empresa)
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entidades');
    }
};
