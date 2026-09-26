<?php

namespace App\Http\Controllers;

use App\Services\PosicionService;
use Illuminate\Http\Request;

class PosicionController extends Controller
{
    public function __construct(private PosicionService $posicion_service) {}

    public function listado()
    {
        return [
            "posicions" => $this->posicion_service->listado()
        ];
    }
}
