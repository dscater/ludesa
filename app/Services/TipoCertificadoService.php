<?php

namespace App\Services;

use App\Models\Certificado;
use App\Models\CertificadoDetalle;
use App\Models\CertificadoEmitido;
use App\Services\HistorialAccionService;
use App\Models\TipoCertificado;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class TipoCertificadoService
{
    private $modulo = "TIPO DE CERTIFICADOS";

    public function __construct(private  CargarArchivoService $cargarArchivoService, private HistorialAccionService $historialAccionService) {}

    public function listado(): Collection
    {
        $tipo_certificados = TipoCertificado::select("tipo_certificados.*")->get();
        return $tipo_certificados;
    }
    /**
     * Lista de tipo_certificados paginado con filtros
     *
     * @param integer $length
     * @param integer $page
     * @param string $search
     * @param array $columnsSerachLike
     * @param array $columnsFilter
     * @return LengthAwarePaginator
     */
    public function listadoPaginado(int $length, int $page, string $search, array $columnsSerachLike = [], array $columnsFilter = [], array $columnsBetweenFilter = [], array $orderBy = []): LengthAwarePaginator
    {
        $tipo_certificados = TipoCertificado::select("tipo_certificados.*");

        // Filtros exactos
        foreach ($columnsFilter as $key => $value) {
            if (!is_null($value)) {
                $tipo_certificados->where("tipo_certificados.$key", $value);
            }
        }

        // Filtros por rango
        foreach ($columnsBetweenFilter as $key => $value) {
            if (isset($value[0], $value[1])) {
                $tipo_certificados->whereBetween("tipo_certificados.$key", $value);
            }
        }

        // Búsqueda en múltiples columnas con LIKE
        if (!empty($search) && !empty($columnsSerachLike)) {
            $tipo_certificados->where(function ($query) use ($search, $columnsSerachLike) {
                foreach ($columnsSerachLike as $col) {
                    $query->orWhere("$col", "LIKE", "%$search%");
                }
            });
        }

        // Ordenamiento
        foreach ($orderBy as $value) {
            if (isset($value[0], $value[1])) {
                $tipo_certificados->orderBy($value[0], $value[1]);
            }
        }


        $tipo_certificados = $tipo_certificados->paginate($length, ['*'], 'page', $page);
        return $tipo_certificados;
    }

    /**
     * Crear tipo_certificado
     *
     * @param array $datos
     * @return TipoCertificado
     */
    public function crear(array $datos): TipoCertificado
    {
        $tipo_certificado = TipoCertificado::create([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "precio" => $datos["precio"],
            "descripcion" => mb_strtoupper($datos["descripcion"]) ?? null,
            "fecha_registro" => date("Y-m-d")
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN TIPO DE CERTIFICADO", $tipo_certificado);

        return $tipo_certificado;
    }

    /**
     * Actualizar tipo_certificado
     *
     * @param array $datos
     * @param TipoCertificado $tipo_certificado
     * @return TipoCertificado
     */
    public function actualizar(array $datos, TipoCertificado $tipo_certificado): TipoCertificado
    {
        $old_tipo_certificado = clone $tipo_certificado;

        $tipo_certificado->update([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "precio" => $datos["precio"],
            "descripcion" => mb_strtoupper($datos["descripcion"]) ?? null,
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN TIPO DE CERTIFICADO", $old_tipo_certificado, $tipo_certificado->withoutRelations());

        return $tipo_certificado;
    }

    /**
     * Eliminar tipo_certificado
     *
     * @param TipoCertificado $tipo_certificado
     * @return boolean
     */
    public function eliminar(TipoCertificado $tipo_certificado): bool|Exception
    {
        // verificar usos
        $usos = CertificadoDetalle::where("tipo_certificado_id", $tipo_certificado->id)->get();
        if (count($usos) > 0) {
            throw ValidationException::withMessages([
                'error' =>  "No es posible eliminar este registro porque esta siendo utilizado por otros registros",
            ]);
        }
        $usos = CertificadoEmitido::where("tipo_certificado_id", $tipo_certificado->id)->get();
        if (count($usos) > 0) {
            throw ValidationException::withMessages([
                'error' =>  "No es posible eliminar este registro porque esta siendo utilizado por otros registros",
            ]);
        }

        $old_tipo_certificado = clone $tipo_certificado;
        $tipo_certificado->delete();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN TIPO DE CERTIFICADO", $old_tipo_certificado, $tipo_certificado);

        return true;
    }
}
