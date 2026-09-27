<?php

namespace App\Services;

class CostoDerechoService
{
    public function listado()
    {
        return [
            "FUTSAL" => [
                "derecho" => 80,
            ],
            "CAMPO" => [
                "derecho" => 120,
            ]
        ];
    }

    public function getCostosTipo($tipo)
    {
        $lista = $this->listado();
        return $lista[$tipo];
    }
}
