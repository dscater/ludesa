<?php

namespace App\Services;

class PosicionService
{
    public function listado()
    {
        return [
            [
                "value" => "PORTERO",
                "label" => "PORTERO",
                "icon" => "fa fa-check",
            ],
            [
                "value" => "DEFENSA",
                "label" => "DEFENSA",
                "icon" => "fa fa-check",
            ],
            [
                "value" => "MEDIOCAMPO",
                "label" => "MEDIOCAMPO",
                "icon" => "fa fa-check",
            ],
            [
                "value" => "DELANTERO",
                "label" => "DELANTERO",
                "icon" => "fa fa-check",
            ],
        ];
    }
}
