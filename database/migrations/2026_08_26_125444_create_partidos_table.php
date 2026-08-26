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
        Schema::create('partidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("campeonato_id");
            $table->unsignedBigInteger("local_id");
            $table->unsignedBigInteger("visitante_id");
            $table->integer("goles_local")->default(0);
            $table->integer("goles_visitante")->default(0);
            $table->unsignedBigInteger("ganador_id")->nullable();
            $table->decimal("total_local", 24, 2);
            $table->boolean("pago_local");
            $table->decimal("total_visitante", 24, 2);
            $table->boolean("pago_visitante");
            $table->date("fecha");
            $table->time("hora");
            $table->string("estado"); //PENDIENTE, FINALIZADO
            $table->timestamps();

            $table->foreign("campeonato_id")->on("campeonatos")->references("id");
            $table->foreign("local_id")->on("carreras")->references("id");
            $table->foreign("visitante_id")->on("carreras")->references("id");
            $table->foreign("ganador_id")->on("carreras")->references("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }
};
