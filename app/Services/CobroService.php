<?php

namespace App\Services;

use App\Models\Certificado;
use App\Models\CertificadoDetalle;
use App\Services\HistorialAccionService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CobroService
{
    private $modulo = "CERTIFICADOS PAGOS";

    public function __construct(private HistorialAccionService $historialAccionService, private PagoService $pago_service) {}

    public function crear(Certificado $certificado, $datos)
    {
        $certificado->tipo_pago = $datos["tipo_pago"];
        $certificado->save();

        // detalles
        foreach ($datos["certificado_detalles"] as $key => $item) {
            $saldo = 0;
            $cancelado = $item["precio"];
            $certificado_detalle = CertificadoDetalle::findOrFail($item["id"]);
            $certificado_detalle->update([
                "saldo" => $saldo,
                "cancelado" => $cancelado,
            ]);

            // PAGO POR DETALLE
            if (!$cancelado) {
                throw new Exception("No se encontro el monto cancelado");
            }
            $existe_pago = $this->pago_service->obtienePagoAnuladoExistente($certificado_detalle);
            if (!$existe_pago) {
                // si no hay pago registrado crear el pago
                $this->pago_service->crear([
                    "registro_id" => $certificado_detalle->id,
                    "modulo" => "CertificadoDetalle",
                    "monto" => $cancelado,
                    "tipo_pago" => $datos["tipo_pago"],
                    "descripcion" => "PAGO POR CERTIFICADO",
                    "cliente_id" => $certificado->cliente_id,
                    "medico_id" => $certificado->user_id
                ]);
            } else {
                // si hay pago registrado, reestablecer
                $this->pago_service->actualizarPagoCertificadoDetalle($certificado_detalle, $certificado_detalle->cancelado, $saldo);
            }
        }
        // actualizar Saldo
        $certificado->cancelado = $certificado->total;
        $certificado->saldo = 0;
        $certificado->save();
        return $certificado;
    }
}
