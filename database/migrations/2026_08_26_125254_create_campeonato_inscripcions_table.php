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
