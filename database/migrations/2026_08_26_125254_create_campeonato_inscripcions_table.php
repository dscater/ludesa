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
        Schema::create('campeonato_inscripcions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("campeonato_id");
            $table->unsignedBigInteger("carrera_id");
            $table->int("pj")->default(0); //partidos jugados
            $table->int("pts")->default(0); //puntos
            $table->int("gf")->default(0); //goles a favor
            $table->int("gc")->default(0); //goles en contra
            $table->int("dg")->default(0); //diferencia de goles
            $table->int("pg")->default(0); //partidos ganados
            $table->int("pe")->default(0); //partidos empatados
            $table->int("pp")->default(0); //partidos perdidos
            $table->string("estado")->default("PARTICIPANTE"); //PARTICIPANTE, GANADOR
            $table->date("fecha"); // de inscripcion
            $table->time("hora"); // de inscripcion
            $table->timestamps();

            $table->foreign("campeonato_id")->on("campeonatos")->references("id");
            $table->foreign("carrera_id")->on("carreras")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campeonato_inscripcions');
    }
};
