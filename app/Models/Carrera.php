<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $fillable = [
        "nombre",
        "descripcion",
    ];

    public function campeonato_inscripcions()
    {
        return $this->hasMany(CampeonatoInscripcion::class, "carrera_id");
    }
}
