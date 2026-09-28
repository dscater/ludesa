<?php

namespace App\Http\Controllers;

use App\Models\PartidoDetalle;
use App\Services\PartidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartidoDetalleController extends Controller
{
    public function __construct(
        private PartidoService $partidoService,
    ) {}


    public function actualizaDatosDetalle(PartidoDetalle $partido_detalle, Request $request)
    {
        DB::beginTransaction();
        try {
            // actualizar partido_detalle
            $partido_detalle = $this->partidoService->actualizaDatosDetalle($partido_detalle, $request->col, $request->data);
            DB::commit();
            return response()->JSON([
                "sw" => true,
                "message" => "Registros actualizados",
                "partido_detalle" => $partido_detalle
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }
}
