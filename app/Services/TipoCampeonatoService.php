<?php

namespace App\Services;

class TipoCampeonatoService
{
    public function listado()
    {
        return [
            [
                "value" => "FUTSAL",
                "label" => "FUTSAL",
                "icon" => "fa fa-court-sport",
            ],
            [
                "value" => "CAMPO",
                "label" => "CAMPO",
                "icon" => "fa fa-futbol",
            ],
        ];
    }
}
