<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $fillable = [
        "nombre",
        "logo",
        "descripcion",
    ];
    protected $appends = ["url_logo", "logo_b64"];

    public function getUrlLogoAttribute()
    {
        if ($this->logo) {
            return asset("imgs/carreras/" . $this->logo);
        }
        return asset("imgs/carreras/default.png");
    }

    public function getLogoB64Attribute()
    {
        $path = public_path("imgs/carreras/" . $this->logo);
        if (!$this->logo || !file_exists($path)) {
            $path = public_path("imgs/carreras/default.png");
        }
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }

    public function campeonato_inscripcions()
    {
        return $this->hasMany(CampeonatoInscripcion::class, "carrera_id");
    }
}
