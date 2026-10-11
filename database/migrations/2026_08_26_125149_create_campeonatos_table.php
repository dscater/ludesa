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
            $table->boolean("inicio_fechas")->default(0); //0: sin generar, 1:fechas generadas
            $table->text("descripcion")->nullable();
            $table->string("estado")->default("VIGENTE"); //VIGENTE, FINALIZADO
            $table->date("fecha_registro")->nullable();
            $table->date("fecha_fin")->nullable();
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
