<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampeonatoInscripcion extends Model
{
    protected $fillable = [
        "campeonato_id",
        "carrera_id",
        "total_inscripcion",
        "pago_inscripcion",
        "pj", // partidos jugados
        "pts", //puntos
        "gf", // goles a favor
        "gc", // goles en contra
        "dg", // diferencia de goles
        "pg", // partidos ganados
        "pe", // partidos empatados
        "pp", // partidos perdidos
        "estado", //PARTICIPANTE, GANADOR
        "fecha", // de inscripcion
        "hora" // de inscripcion
    ];

    protected $appends = ["fecha_t", "fecha_hora_t"];

    public function getFechaTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha));
    }

    public function getFechaHoraTAttribute()
    {
        return date("d/m/Y H:i:s", strtotime($this->fecha . ' ' . $this->hora));
    }

    public function campeonato()
    {
        return $this->belongsTo(Campeonato::class, 'campeonato_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function carrera_jugadors()
    {
        return $this->hasMany(CarreraJugador::class, 'campeonato_inscripcion_id');
    }

    public function partidos_local()
    {
        return $this->hasMany(Partido::class, 'ci_local_id');
    }

    public function partidos_visitante()
    {
        return $this->hasMany(Partido::class, 'ci_visitante_id');
    }

    public function partido_detalles()
    {
        return $this->hasMany(PartidoDetalle::class, 'campeonato_inscripcion_id');
    }
}
