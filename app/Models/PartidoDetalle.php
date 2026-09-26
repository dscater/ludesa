<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartidoDetalle extends Model
{
    protected $fillable = [
        "campeonato_id",
        "partido_id",
        "campeonato_inscripcion_id",
        "carrera_id",
        "carrera_jugador_id",
        "titular",
        "amarillas",
        "total_amarillas",
        "pagado_amarillas",
        "rojas",
        "total_rojas",
        "pagado_rojas",
        "goles",
    ];

    public function campeonato()
    {
        return $this->belongsTo(Campeonato::class, 'campeonato_id');
    }
    public function partido()
    {
        return $this->belongsTo(Partido::class, 'partido_id');
    }
    public function campeonato_inscripcion()
    {
        return $this->belongsTo(CampeonatoInscripcion::class, 'campeonato_inscripcion_id');
    }
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }
    public function carrera_jugador()
    {
        return $this->belongsTo(CarreraJugador::class, 'carrera_jugador_id');
    }
}
