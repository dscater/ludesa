<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campeonato extends Model
{
    protected $fillable = [
        "nombre",
        "periodo",
        "gestion",
        "tipo",
        "descripcion",
        "estado",
    ];
}
