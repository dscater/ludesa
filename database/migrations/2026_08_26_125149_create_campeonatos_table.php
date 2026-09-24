<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campeonatos', function (Blueprint $table) {
            $table->id();
            $table->string("nombre");
            $table->integer("periodo");
            $table->integer("gestion");
            $table->string("tipo"); //FUTSAL, CAMPO
            $table->text("descripcion")->nullable();
            $table->string("estado")->default("VIGENTE"); //VIGENTE, FINALIZADO
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campeonatos');
    }
};
