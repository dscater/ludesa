<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarreraJugador extends Model
{
    protected $fillable = [
        "campeonato_id",
        "carrera_id",
        "campeonato_inscripcion_id",
        "jugador_id",
        "posicion",
        "nro",
        "fecha_registro"
    ];

    protected $appends = ["fecha_registro_t"];

    public function getFechaRegistroTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_registro));
    }

    public function campeonato()
    {
        return $this->belongsTo(Campeonato::class, 'campeonato_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function campeonato_inscripcion()
    {
        return $this->belongsTo(CampeonatoInscripcion::class, 'campeonato_inscripcion_id');
    }

    public function jugador()
    {
        return $this->belongsTo(Jugador::class, 'jugador_id');
    }
}
