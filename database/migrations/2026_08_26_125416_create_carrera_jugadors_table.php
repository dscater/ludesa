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
        Schema::create('carrera_jugadors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("campeonato_id");
            $table->unsignedBigInteger("carrera_id");
            $table->unsignedBigInteger("jugador_id");
            $table->string("posicion");
            $table->string("nro");
            $table->timestamps();

            $table->foreign("campeonato_id")->on("campeonatos")->references("id");
            $table->foreign("carrera_id")->on("carreras")->references("id");
            $table->foreign("jugador_id")->on("jugadors")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrera_jugadors');
    }
};
