<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campeonato extends Model
{
    protected $fillable = [
        "nombre",
        "periodo",
        "gestion",
        "tipo", //FUTSAL, CAMPO
        "descripcion",
        "estado", //VIGENTE, FINALIZADO
        "fecha_registro",
        "fecha_fin",
    ];

    protected $appends = ["fecha_registro_t", "fecha_fin_t", "full_name"];

    public function getFullNameAttribute()
    {
        return $this->periodo . " - " . $this->gestion . ": " . $this->nombre . " (" . $this->tipo . ")";
    }

    public function getFechaFinTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_fin));
    }

    public function getFechaRegistroTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_registro));
    }
}
