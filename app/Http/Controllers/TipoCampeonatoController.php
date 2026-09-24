<?php

namespace App\Http\Controllers;

use App\Services\TipoCampeonatoService;
use Illuminate\Http\Request;

class TipoCampeonatoController extends Controller
{
    public function __construct(private TipoCampeonatoService $tipo_campeonato_service) {}

    public function listado()
    {
        return [
            "tipo_campeonatos" => $this->tipo_campeonato_service->listado()
        ];
    }
}
