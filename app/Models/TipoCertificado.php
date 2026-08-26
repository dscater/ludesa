<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCertificado extends Model
{
    protected $fillable = [
        "nombre",
        "precio",
        "descripcion",
        "fecha_registro",
    ];

    protected $appends = ["fecha_registro_t"];

    public function getFechaRegistroTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_registro));
    }
}
