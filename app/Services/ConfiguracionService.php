<?php

namespace App\Services;

use App\Models\Configuracion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConfiguracionService
{
    public function __construct(private CargarArchivoService $cargarArchivoService) {}

    /**
     * Actualizar configuracion
     *
     * @param array $datos
     * @param Configuracion $configuracion
     * @return Configuracion
     */
    public function actualizar(array $datos, Configuracion $configuracion): Configuracion
    {
        $old_area = clone $configuracion;

        $configuracion = Configuracion::first();
        Log::debug("AAA");

        if (!$configuracion) {
            $configuracion = Configuracion::create([
                "nombre_sistema" => $datos["nombre_sistema"],
                "alias" => $datos["alias"],
                "razon_social" => $datos["razon_social"],
                "nit" => $datos["nit"],
                "dir" => $datos["dir"],
                "fono" => $datos["fono"],
                "actividad" => $datos["actividad"],
                "correo" => $datos["correo"],
            ]);
        } else {
            $configuracion->update([
                "nombre_sistema" => $datos["nombre_sistema"],
                "alias" => $datos["alias"],
                "razon_social" => $datos["razon_social"],
                "nit" => $datos["nit"],
                "dir" => $datos["dir"],
                "fono" => $datos["fono"],
                "actividad" => $datos["actividad"],
                "correo" => $datos["correo"],
            ]);
        }

        Log::debug("BBB");
        // cargar logo
        if ($datos["logo"] && !is_string($datos["logo"])) {
            $this->cargarLogo($configuracion, $datos["logo"]);
        }
        Log::debug("BBB");

        return $configuracion;
    }

    public function cargarLogo(Configuracion $configuracion, UploadedFile $logo): void
    {
        if ($configuracion->logo) {
            \File::delete(public_path("imgs/" . $configuracion->logo));
        }
        $nombre = $configuracion->id . time();
        $configuracion->logo = $this->cargarArchivoService->cargarArchivo($logo, public_path("imgs"), $nombre);
        $configuracion->save();
    }
}
