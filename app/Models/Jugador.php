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

    protected $appends = ["url_foto", "foto_b64", "fecha_registro_t"];

    public function getFechaRegistroTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_registro));
    }

    public function getFotoB64Attribute()
    {
        $path = public_path("imgs/jugadors/" . $this->foto);
        if (!$this->foto || !file_exists($path)) {
            $path = public_path("imgs/jugadors/default.png");
        }
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
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
