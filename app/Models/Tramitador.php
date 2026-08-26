<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tramitador extends Model
{
    protected $fillable = [
        "nombre",
        "ci",
        "ci_exp",
        "cel",
        "fecha_registro",
        "user_id"
    ];

    protected $appends = ["fecha_registro_t", "full_ci"];

    public function getFullCiAttribute()
    {
        return $this->ci . ' ' . $this->ci_exp;
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
