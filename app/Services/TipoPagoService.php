<?php

namespace App\Services;


class TipoPagoService
{
    public function listado()
    {
        return       [
            [
                "value" => "EFECTIVO",
                "label" => "EFECTIVO",
                "icon" => "fa fa-money-bill",

            ],
            [
                "value" => "QR",
                "label" => "QR",
                "icon" => "fa fa-qrcode",

            ]
        ];
    }
}
