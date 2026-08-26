<?php

namespace App\Services;

use App\Models\Certificado;
use App\Services\HistorialAccionService;
use App\Models\Tramitador;
use App\Models\Tramite;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TramitadorService
{
    private $modulo = "TRAMITADORES";

    public function __construct(private  CargarArchivoService $cargarArchivoService, private HistorialAccionService $historialAccionService) {}

    public function listado(): Collection
    {
        $tramitadors = Tramitador::select("tramitadors.*")->get();
        return $tramitadors;
    }
    /**
     * Lista de tramitadors paginado con filtros
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
        $tramitadors = Tramitador::select("tramitadors.*")
            ->with("user:id,nombre,paterno,materno");

        $tramitadors->where(function ($query) use ($search) {
            $query->buscarNombre($search);
            $query->orWhereHas("user", function ($sub) use ($search) {
                $sub->whereRaw(
                    "CONCAT(nombre, ' ', paterno, ' ', materno) LIKE ?",
                    ["%{$search}%"]
                );
            });
        });

        // Ordenamiento
        foreach ($orderBy as $value) {
            if (isset($value[0], $value[1])) {
                $tramitadors->orderBy($value[0], $value[1]);
            }
        }

        $tramitadors = $tramitadors->paginate($length, ['*'], 'page', $page);
        return $tramitadors;
    }

    /**
     * Crear tramitador
     *
     * @param array $datos
     * @return Tramitador
     */
    public function crear(array $datos): Tramitador
    {
        $tramitador = Tramitador::create([
            "nombre" => mb_strtoupper($datos['nombre']),
            "ci" => mb_strtoupper($datos['ci']),
            "ci_exp" => mb_strtoupper($datos['ci_exp']),
            "cel" => $datos['cel'],
            "fecha_registro" => date("Y-m-d"),
            "user_id" => Auth::user()->id,
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN TRAMITADOR", $tramitador);

        return $tramitador;
    }

    /**
     * Actualizar tramitador
     *
     * @param array $datos
     * @param Tramitador $tramitador
     * @return Tramitador
     */
    public function actualizar(array $datos, Tramitador $tramitador): Tramitador
    {
        $old_tramitador = clone $tramitador;

        $tramitador->update([
            "nombre" => mb_strtoupper($datos['nombre']),
            "ci" => mb_strtoupper($datos['ci']),
            "ci_exp" => mb_strtoupper($datos['ci_exp']),
            "cel" => $datos['cel'],
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN TRAMITADOR", $old_tramitador, $tramitador->withoutRelations());

        return $tramitador;
    }

    /**
     * Eliminar tramitador
     *
     * @param Tramitador $tramitador
     * @return boolean
     */
    public function eliminar(Tramitador $tramitador): bool|Exception
    {
        $old_tramitador = clone $tramitador;

        $usos = Certificado::where("tramitador_id", $tramitador->id)->count();
        if ($usos > 0) {
            throw new Exception("No se puede eliminar el registro porque esta siendo utilizado");
        }

        $tramitador->delete();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN TRAMITADOR", $old_tramitador, $tramitador);

        return true;
    }
}
