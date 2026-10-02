<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CostoInscripcionService;

class CostoInscripcionController extends Controller
{
    public function __construct(private CostoInscripcionService $costoInscripcionService) {}

    public function getCostoByTipo(Request $request)
    {
        $tipo = $request->input('tipo');
        $costo = $this->costoInscripcionService->getCostosTipo($tipo);
        return response()->json(['costo' => $costo]);
    }
}
