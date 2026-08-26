<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificadoEmitido extends Model
{
    protected $fillable = [
        "fecha",
        "user_id",
        "tipo_certificado_id",
        "conteo",
    ];

    protected $appends = ["fecha_t"];

    public function getFechaTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha));
    }
}
