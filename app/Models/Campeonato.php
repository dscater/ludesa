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
    ];
}
