<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Certificado extends Model
{
    protected $fillable = [
        "cliente_id",
        "total",
        "cancelado",
        "saldo",
        "tipo_pago",
        "user_id",
        "inicio_id",
        "sucursal_id",
        "tipo", //NORMAL: registrado por medico, TRAMITE: grupo
        "tramitador_id",
        "fecha_inicio",
        "hora_inicio",
        "fecha_fin",
        "hora_fin",
        "fecha_registro",
        "hora_registro",
        "estado", // 0: PENDIENTE, 1: ATENDIDO
        "status",
    ];

    protected $appends = ["fecha_registro_t", "fecha_inicio_t", "fecha_fin_t"];

    public function getEditableAttribute()
    {
        $editable = false;

        if (Auth::check()) {
            $user = Auth::user();
            $editable = true;
            if ($user->tipo == 'SECRETARIA') {
                if ($user->id != $this->inicio_id) {
                    $editable = false;
                }
            }
        }
        return $editable;
    }

    public function getFechaInicioTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_inicio));
    }
    public function getFechaFinTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_fin));
    }

    public function getFechaRegistroTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_registro));
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function inicio()
    {
        return $this->belongsTo(User::class, 'inicio_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function certificado_detalles()
    {
        return $this->hasMany(CertificadoDetalle::class, 'certificado_id');
    }

    public function tramitador()
    {
        return $this->belongsTo(Tramitador::class, 'tramitador_id');
    }
}
