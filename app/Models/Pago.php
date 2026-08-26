<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        "registro_id",
        "modulo",
        "monto",
        "tipo_pago",
        "descripcion",
        "fecha",
        "hora",
        "cliente_id",
        "user_id",
        "medico_id",
        "sucursal_id",
        "verificado",
        "fecha_verificado",
        "hora_verificado",
        "status"
    ];

    protected $appends = ["fecha_t", "fecha_verificado_t"];

    public function getFechaVerificadoTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha_verificado));
    }

    public function getFechaTAttribute()
    {
        return date("d/m/Y", strtotime($this->fecha));
    }

    public function certificado_detalle()
    {
        return $this->belongsTo(CertificadoDetalle::class, 'registro_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
