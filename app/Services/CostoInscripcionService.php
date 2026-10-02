<?php

namespace App\Services;

class CostoInscripcionService
{
    public function listado()
    {
        return [
            "FUTSAL" => 100,
            "CAMPO" => 200,
        ];
    }

    public function getCostosTipo($tipo)
    {
        $lista = $this->listado();
        return $lista[$tipo];
    }
}
