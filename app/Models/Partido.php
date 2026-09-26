<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    protected $fillable = [
        "campeonato_id",
        "ci_local_id",
        "local_id",
        "ci_visitante_id",
        "visitante_id",
        "goles_local",
        "goles_visitante",
        "ganador_id",
        "total_local",
        "pago_local",
        "total_visitante",
        "pago_visitante",
        "fecha",
        "hora",
        "estado", //PENDIENTE, INICIADO, FINALIZADO
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

    public function ci_local()
    {
        return $this->belongsTo(CampeonatoInscripcion::class, 'ci_local_id');
    }
    public function ci_visitante()
    {
        return $this->belongsTo(CampeonatoInscripcion::class, 'ci_visitante_id');
    }

    public function partido_detalles()
    {
        return $this->hasMany(PartidoDetalle::class, 'partido_id');
    }
}
