<?php

namespace App\Services;

use App\Models\CertificadoEmitido;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CertificadoEmitidoService
{
    private $modulo = "CERTIFICADOS EMITIDOS";

    public function __construct() {}

    /**
     * Crear certificado
     *
     * @param array $datos
     * @return CertificadoEmitido
     */
    public function actualizarCertificadoEmitido($tipo_certificado_id, $fecha_actual, $user_id, $old_tipo_certificado_id = null)
    {
        $certificado_emitido = CertificadoEmitido::where("tipo_certificado_id", $tipo_certificado_id)
            ->where("fecha", $fecha_actual)
            ->where("user_id", $user_id)
            ->get()
            ->first();

        if (!$certificado_emitido) {
            return null;
        }

        if (!$old_tipo_certificado_id) {
            // NUEVO
            if (!$certificado_emitido) {
                $certificado_emitido = CertificadoEmitido::create([
                    "fecha" => $fecha_actual,
                    "user_id" => $user_id,
                    "tipo_certificado_id" => $tipo_certificado_id,
                    "conteo" => 1,
                ]);
            } else {
                $certificado_emitido->conteo = (int)$certificado_emitido->conteo + 1;
                $certificado_emitido->save();
            }
        } else {
            // EDIT
            if ($certificado_emitido->tipo_certificado_id != $tipo_certificado_id) {
                // SE CAMBIO EL TIPO DESCONTAR EL TIPO ANTERIOR Y AUMENTAR EL NUEVO TIPO

                // descontar
                $certificado_emitido->conteo = (int)$certificado_emitido->conteo - 1;
                $certificado_emitido->save();

                // buscar nuevo
                $certificado_emitido_nuevo = CertificadoEmitido::where("tipo_certificado_id", $tipo_certificado_id)
                    ->where("fecha", $fecha_actual)
                    ->where("user_id", $user_id)
                    ->get()
                    ->first();
                if (!$certificado_emitido_nuevo) {
                    $certificado_emitido_nuevo = CertificadoEmitido::create([
                        "fecha" => $fecha_actual,
                        "user_id" => $user_id,
                        "tipo_certificado_id" => $tipo_certificado_id,
                        "conteo" => 1,
                    ]);
                } else {
                    $certificado_emitido_nuevo->conteo = (int)$certificado_emitido_nuevo->conteo + 1;
                    $certificado_emitido_nuevo->save();
                }
                $certificado_emitido = $certificado_emitido_nuevo;
            }
            // caso contrario no hacer nada
        }

        return $certificado_emitido;
    }

    public function descontarCertificadoEmitido($tipo_certificado_id, $fecha_actual, $user_id)
    {
        $certificado_emitido = CertificadoEmitido::where("tipo_certificado_id", $tipo_certificado_id)
            ->where("fecha", $fecha_actual)
            ->where("user_id", $user_id)
            ->get()
            ->first();

        if ($certificado_emitido) {
            $certificado_emitido->conteo = (int)$certificado_emitido->conteo - 1;
            $certificado_emitido->save();
            return $certificado_emitido;
        }
        return null;
    }
}
