<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificadoDetalle extends Model
{
    protected $fillable = [
        "certificado_id",
        "categoria",
        "precio",
        "cancelado",
        "saldo",
        "tipo_certificado_id",
        "sucursal_id",
        "user_id",
        "fecha_inicio",
        "hora_inicio",
        "fecha_fin",
        "hora_fin",
        "estado", // 0: PENDIENTE, 1: ATENDIDO
        "archivo",
    ];

    protected $appends = ["url_archivo", "name", "con_saldo", "fecha_inicio_t", "fecha_fin_t"];

    public function getFechaInicioTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_inicio));
    }
    public function getFechaFinTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_fin));
    }

    public function getConSaldoAttribute()
    {
        if ($this->saldo > 0) {
            // CHECBOX DESMARCADO
            // EXISTE SALDO
            return false;
        }
        return true;
    }

    public function getNameAttribute()
    {
        return $this->archivo;
    }

    public function getUrlArchivoAttribute()
    {
        return asset("files/certificados/" . $this->archivo);
    }

    public function certificado()
    {
        return $this->belongsTo(Certificado::class, 'certificado_id');
    }

    public function tipo_certificado()
    {
        return $this->belongsTo(TipoCertificado::class, 'tipo_certificado_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'registro_id')->where("modulo", "CertificadoDetalle");
    }
}
