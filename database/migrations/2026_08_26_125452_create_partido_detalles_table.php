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
        Schema::create('partido_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("campeonato_id");
            $table->unsignedBigInteger("partido_id");
            $table->unsignedBigInteger("campeonato_inscripcion_id");
            $table->unsignedBigInteger("carrera_id");
            $table->unsignedBigInteger("carrera_jugador_id");
            $table->boolean("titular")->default(0);
            $table->int("amarillas")->default(0); // AMARILLAS 
            $table->decimal("total_amarillas", 24, 2)->default(0);
            $table->boolean("pagado_amarillas")->default(0);
            $table->int("rojas")->default(0); // ROJAS 
            $table->decimal("total_rojas", 24, 2)->default(0);
            $table->boolean("pagado_rojas")->default(0);
            $table->int("goles");
            $table->timestamps();


            $table->foreign("campeonato_id")->on("campeonatos")->references("id");
            $table->foreign("partido_id")->on("partidos")->references("id");
            $table->foreign("campeonato_inscripcion_id")->on("campeonato_inscripcions")->references("id");
            $table->foreign("carrera_id")->on("carreras")->references("id");
            $table->foreign("carrera_jugador_id")->on("carrera_jugadors")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partido_detalles');
    }
};
