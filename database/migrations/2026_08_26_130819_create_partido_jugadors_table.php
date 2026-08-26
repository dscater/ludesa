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
        Schema::create('partido_jugadors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("campeonato_id");
            $table->unsignedBigInteger("partido_id");
            $table->unsignedBigInteger("carrera_jugador_id");
            $table->timestamps();

            $table->foreign("campeonato_id")->on("campeonatos")->references("id");
            $table->foreign("partido_id")->on("partidos")->references("id");
            $table->foreign("carrera_jugador_id")->on("carrera_jugadors")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partido_jugadors');
    }
};
