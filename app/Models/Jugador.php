<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jugador extends Model
{
    protected $fillable = [
        "nombres",
        "apes",
        "ci",
        "correo",
        "fono",
        "dir",
        "foto",
        "fecha_registro"
    ];

    protected $appends = ["url_foto", "fecha_registro_t"];

    public function getFechaRegistroTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_registro));
    }

    public function getUrlFotoAttribute()
    {
        if ($this->foto) {
            return asset("imgs/jugadors/" . $this->foto);
        }
        return asset("imgs/jugadors/default.png");
    }

    public function carrera_jugadors()
    {
        return $this->hasMany(CarreraJugador::class, "jugador_id");
    }
}
