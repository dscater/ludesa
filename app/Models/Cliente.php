<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Cliente extends Model
{
    protected $fillable = [
        "nombre",
        "paterno",
        "materno",
        "ci",
        "ci_exp",
        "complemento",
        "fecha_nac",
        "edad",
        "cel",
        "fecha_registro",
        "status",
        "user_id"
    ];

    protected $appends = ["fecha_registro_t", "full_ci", "fecha_nac_t", "full_name"];

    public function getFullNameAttribute()
    {
        return $this->nombre . ' ' . $this->paterno . ($this->materno ? ' ' . $this->materno : '');
    }
    public function getFechaNacTAttribute()
    {
        if ($this->fecha_nac) {
            return date("d/m/Y", strtotime($this->fecha_nac));
        }
        return "";
    }
    public function getFullCiAttribute()
    {
        return $this->ci . ($this->complemento ? '-' . $this->complemento : '') . ' ' . $this->ci_exp;
    }

    public function getFechaRegistroTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_registro));
    }

    public function scopeBuscarNombre($query, $texto)
    {
        if (!$texto) return $query;

        $palabras = explode(' ', $texto);

        foreach ($palabras as $palabra) {
            $query->where(function ($q) use ($palabra) {
                $q->where('nombre', 'like', "%$palabra%")
                    ->orWhere('paterno', 'like', "%$palabra%")
                    ->orWhere('materno', 'like', "%$palabra%")
                    ->orWhere('ci', 'like', "%$palabra%");
            });
        }

        return $query;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
