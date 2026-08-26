<?php

namespace App\Services;

use App\Models\Certificado;
use App\Models\CertificadoDetalle;
use App\Models\LoginUser;
use App\Models\Pago;
use App\Services\HistorialAccionService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PagoService
{
    private $modulo = "PAGOS";

    public function __construct(private HistorialAccionService $historialAccionService, private LoginUserService $login_user_service, private TipoPagoService $tipo_pago_service) {}

    public function reporteArqueo(
        $fecha_ini,
        $fecha_fin,
        $sucursal_id,
        $medico_id,
        $user_id,
    ) {
        $pagos = [];
        $suma_tipos = [];
        $suma_tipos_sin_verificar = [];
        $suma_total_tipos = [];
        if ($fecha_ini && $fecha_fin) {
            $pagos = Pago::with([
                "sucursal:id,nombre",
                "user:id,nombre,paterno,materno",
                "medico:id,nombre,paterno,materno",
                "certificado_detalle.tipo_certificado",
                "certificado_detalle.certificado.tramitador",
                "cliente:id,nombre,paterno,materno,ci,ci_exp,complemento",
            ])
                ->whereBetween("fecha_verificado", [$fecha_ini, $fecha_fin]);

            if ($sucursal_id != 'todos' || !$sucursal_id   || $sucursal_id == '') {
                $pagos->where("sucursal_id", $sucursal_id);
            }

            $login_user = $this->login_user_service->verificaSucursal();

            if (Auth::user()->tipo == 'ADMINISTRADOR' || Auth::user()->tipo == 'GERENTE') {
                if ($medico_id) {
                    $pagos->where("medico_id", $medico_id);
                }
            }
            $pagos_sin_verificar = clone $pagos;
            if (Auth::user()->tipo == 'MÉDICO' || Auth::user()->tipo == 'SECRETARIA') {
                $user_id = Auth::user()->id;
                // $pagos->where("user_id", Auth::user()->id);
            }
            if ($user_id) {
                $pagos->where("user_id", $user_id);
            }
            $pagos->where("status", 1);

            // SIN VERIFICAR VOLVER A VALIDAR
            $pagos_sin_verificar->where("status", 1)->where("verificado", 0);
            if (Auth::user()->tipo == 'MÉDICO') {
                $pagos_sin_verificar->where("user_id", Auth::user()->id);
            }

            if (Auth::user()->tipo == 'SECRETARIA') {
                $pagos_sin_verificar->where("sucursal_id", $login_user->sucursal_id);
            }

            $pagos->where("verificado", 1);
            $tipo_pagos = $this->tipo_pago_service->listado();

            foreach ($tipo_pagos as $item) {
                // Inicializar si no existe
                if (!isset($suma_total_tipos[$item["value"]])) {
                    $suma_total_tipos[$item["value"]] = 0;
                }

                $query = clone $pagos;
                $suma_total = $query->where("tipo_pago", $item["value"])->sum("monto");
                $suma_tipos[$item["value"]] = $suma_total;
                $suma_total_tipos[$item["value"]] += (float)$suma_total;
                // sin verificar
                $query2 = clone $pagos_sin_verificar;
                $suma_total = $query2->where("tipo_pago", $item["value"])->sum("monto");
                $suma_tipos_sin_verificar[$item["value"]] = $suma_total;
                $suma_total_tipos[$item["value"]] += (float)$suma_total;
            }

            $pagos_sin_verificar = $pagos_sin_verificar->get();
            $pagos = $pagos->get();
        }

        return [$pagos, $suma_tipos, $pagos_sin_verificar, $suma_tipos_sin_verificar, $suma_total_tipos];
    }

    public function crear($datos)
    {
        $fecha_actual = Carbon::now("America/La_Paz")->format("Y-m-d");
        $hora_actual = Carbon::now("America/La_Paz")->format("H:i:s");

        $login_user = $this->login_user_service->verificaSucursal();
        if (!$login_user) {
            throw new Exception("Error no se encontró la sucursal del usuario");
        }
        $sucursal_id = $login_user->sucursal_id;
        $verificado = $this->obtieneEstadoVerificado($sucursal_id, $login_user);
        $fecha_verificado = $fecha_actual;
        $hora_verificado = $hora_actual;


        $pago = Pago::create([
            "registro_id" => $datos["registro_id"],
            "modulo" => $datos["modulo"],
            "monto" => $datos["monto"],
            "descripcion" => isset($datos["descripcion"]) && $datos["descripcion"] ? $datos["descripcion"] : '',
            "tipo_pago" => $datos["tipo_pago"],
            "cliente_id" => isset($datos["cliente_id"]) ? $datos["cliente_id"] : NULL,
            "fecha" => $fecha_actual,
            "hora" => $hora_actual,
            "user_id" => Auth::user()->id, // ACTUALIZAR SI NO ESTA VERIFICADO EL PAGO (En verificación de Pago)
            "sucursal_id" => $sucursal_id,
            "verificado" => $verificado,
            "fecha_verificado" => $fecha_verificado,
            "hora_verificado" => $hora_verificado,
        ]);

        if (isset($datos["certificado_atendido"])) {
            $pago->medico_id = $datos["certificado_atendido"] == 1 ? Auth::user()->id : NULL;
        }

        if (isset($datos["medico_id"])) {
            $pago->medico_id = $datos["medico_id"];
            $pago->save();
        }

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO EL PAGO DE UN CERTIFICADO", $pago, null);

        return $pago;
    }

    public function obtieneEstadoVerificado($sucursal_id, $login_user)
    {
        $verificado = 0;
        $tipo_usuario = $this->login_user_service->getTipoUsuarioLogin();
        // if ($tipo_usuario == 'SECRETARIA' || !$this->login_user_service->verificaSecretariaSucursal($sucursal_id)) {
        //     // si es secretaria
        //     // o no hay secretaria en sucursal
        //     $verificado = 1;
        // }

        if ($login_user->verifica_pagos == 1) {
            $verificado = 1;
        }

        return $verificado;
    }

    public function asignarMedicoPago(CertificadoDetalle $certificado_detalle)
    {
        $pago = Pago::where("modulo", "CertificadoDetalle")
            ->where("registro_id", $certificado_detalle->id)
            ->where("status", 1)
            ->get()->first();
        if ($pago) {
            // SI NO HAY PAGO EXISTE UN COBRO PENDIENTE
            $pago->medico_id = Auth::user()->id;
            $pago->save();
        }
    }


    public function registrarPagos($ids)
    {
        $fecha_actual = Carbon::now("America/La_Paz")->format("Y-m-d");
        $hora_actual = Carbon::now("America/La_Paz")->format("H:i:s");
        Pago::whereIn("id", $ids)->update([
            "verificado" => 1,
            "fecha_verificado" => $fecha_actual,
            "hora_verificado" => $hora_actual,
            "user_id" => Auth::user()->id // Usuario que recepciono el dinero
        ]);

        return true;
    }

    public function actualizarPagoCertificadoDetalle(CertificadoDetalle $certificado_detalle, $old_cancelado, $nuevo_saldo)
    {
        $pago = $this->obtienePagoAnuladoExistente($certificado_detalle);
        if ($pago) {
            // si el monto del pago es diferente al cancelado del detalle, actualizar el monto del pago
            $pago->tipo_pago = $certificado_detalle->certificado->tipo_pago;
            // $pago->status = 1;
            if ($pago->monto != $certificado_detalle->cancelado && $certificado_detalle->cancelado > 0) {
                $pago->monto = $certificado_detalle->cancelado;
                $pago->save();
                // registrar accion
                $this->historialAccionService->registrarAccion($this->modulo, "ACTUALIZACIÓN", "ACTUALIZÓ EL MONTO DEL PAGO DE UN CERTIFICADO DETALLE DE $old_cancelado A " . $certificado_detalle->cancelado, $pago, null);
            } else {
                $pago->tipo_pago = $certificado_detalle->certificado->tipo_pago;
                $certificado_detalle->saldo = $nuevo_saldo;
                if ((float)$nuevo_saldo > 0) {
                    // ELIMINAR PAGO
                    $certificado_detalle->cancelado = 0;
                    $pago->status = 0;
                } else {
                    // REESTABLECER PAGO
                    if ($pago->status == 0) {
                        $fecha_actual = Carbon::now("America/La_Paz")->format("Y-m-d");
                        $hora_actual = Carbon::now("America/La_Paz")->format("H:i:s");
                        $login_user = $this->login_user_service->verificaSucursal();
                        if (!$login_user) {
                            throw new Exception("Error no se encontró la sucursal del usuario");
                        }
                        // Log::debug("actuliazo");
                        // Log::debug($login_user);

                        $sucursal_id = $login_user->sucursal_id;
                        $verificado = $this->obtieneEstadoVerificado($sucursal_id, $login_user);
                        $pago->fecha_verificado = $fecha_actual;
                        $pago->hora_verificado = $hora_actual;
                        $pago->user_id = $login_user->user_id;
                        $pago->sucursal_id = $sucursal_id;
                        $pago->verificado = $verificado;
                        $pago->status = 1;
                    }
                    $certificado_detalle->cancelado = $certificado_detalle->precio;
                    $pago->monto = $certificado_detalle->precio;
                }
                $certificado_detalle->save();
                // el monto es 0 o no se ha cancelado, eliminar el pago
                $pago->save();
            }
        }
    }

    public function verificaPagoCertificadoDetalle(CertificadoDetalle $certificado_detalle)
    {
        $pago = Pago::where("modulo", "CertificadoDetalle")
            ->where("registro_id", $certificado_detalle->id)
            ->where("status", 1)
            ->get()->first();
        if ($pago) {
            return $pago;
        }
        return null;
    }



    public function obtienePagoAnuladoExistente(CertificadoDetalle $certificado_detalle)
    {
        $pago = Pago::where("modulo", "CertificadoDetalle")
            ->where("registro_id", $certificado_detalle->id)
            ->get()->first();
        if ($pago) {
            return $pago;
        }
        return null;
    }

    /**
     * LISTADO DE PAGOS PARA EL MODULO DE RECEPCION
     *
     * @return void
     */
    public function listadoRecepcionPagos()
    {
        $pagos = Pago::with(["sucursal:id,nombre", "user:id,nombre,paterno,materno"])
            ->where("status", 1)
            ->where("verificado", 0);

        $login_user = $this->login_user_service->verificaSucursal();
        if (!$login_user) {
            throw new Exception("Error no se encontró la sucursal del usuario");
        }
        $sucursal_id = $login_user->sucursal_id;

        if (Auth::user()->tipo != 'ADMINISTRADOR' && Auth::user()->tipo != 'GERENTE') {
            $pagos->where("sucursal_id", $sucursal_id);
        }

        $pagos = $pagos->get();

        return $pagos;
    }

    public function eliminarPagoPorCertificadoDetalle(CertificadoDetalle $certificado_detalle)
    {
        $pago = $this->verificaPagoCertificadoDetalle($certificado_detalle);
        if ($pago) {
            $this->eliminarPago($pago);
        }
    }

    public function eliminarPago(Pago $pago)
    {
        $pago->status = 0;
        $pago->save();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ EL PAGO DE UN CERTIFICADO", $pago, null);

        return true;
    }

    public function restaurarPagoPorCertificadoDetalle(CertificadoDetalle $certificado_detalle)
    {
        $pago = $this->obtienePagoAnuladoExistente($certificado_detalle);
        if ($pago) {
            $this->restaurarPago($pago);
        }
    }

    public function restaurarPago(Pago $pago)
    {
        $pago->status = 1;
        $pago->save();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "RESTAURACIÓN", "RESTAURÓ EL PAGO DE UN CERTIFICADO", $pago, null);

        return true;
    }

    public function eliminarPagoCertificadoDetalle(CertificadoDetalle $certificado_detalle)
    {
        $pago = $this->verificaPagoCertificadoDetalle($certificado_detalle);
        if ($pago) {
            $pago->delete();
        }
    }


    public function eliminaPagoPorCertificadoDetallePermanente(CertificadoDetalle $certificado_detalle)
    {
        $pago = $this->obtienePagoAnuladoExistente($certificado_detalle);
        if ($pago) {
            $this->eliminarPagoPermanente($pago);
        }
    }

    public function eliminarPagoPermanente(Pago $pago)
    {
        $pago->delete();
        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ PERMANENTEMENTE EL PAGO DE UN CERTIFICADO", $pago, null);

        return true;
    }
}
