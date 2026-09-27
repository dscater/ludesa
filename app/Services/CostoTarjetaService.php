<?php

namespace App\Services;

class CostoTarjetaService
{
    public function listado()
    {
        return [
            "FUTSAL" => [
                "amarilla" => 30,
                "roja" => 90,
            ],
            "CAMPO" => [
                "amarilla" => 30,
                "roja" => 90,
            ]
        ];
    }

    public function getCostoTarjetaPorTipo($tipo, $tarjeta)
    {
        $lista = $this->listado();
        return $lista[$tipo][$tarjeta];
    }

    public function getCostosTipo($tipo)
    {
        $lista = $this->listado();
        return $lista[$tipo];
    }
}
