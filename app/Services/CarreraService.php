<?php

namespace App\Services;

use App\Models\CampeonatoInscripcion;
use App\Services\HistorialAccionService;
use App\Models\Carrera;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CarreraService
{
    private $modulo = "CARRERAS";

    public function __construct(private  CargarArchivoService $cargarArchivoService, private HistorialAccionService $historialAccionService) {}

    public function listado(
        $campeonato_id = null,
        $sin_inscripcion = false,
        $carrera_id = null
    ): Collection {
        $carreras = Carrera::select("carreras.*");

        if ($campeonato_id) {
            // Log::debug($sin_inscripcion);
            if ($sin_inscripcion) {
                // Carreras que NO están inscritas en este campeonato
                $carreras->where(function ($query) use ($campeonato_id, $carrera_id) {
                    $query->whereDoesntHave("campeonato_inscripcions", function ($q) use ($campeonato_id) {
                        $q->where("campeonato_id", $campeonato_id);
                    });

                    // Si estamos editando, incluir el id
                    if ($carrera_id) {
                        $query->orWhere("carreras.id", $carrera_id);
                    }
                });
            } else {
                // Carreras que SÍ están inscritas en este campeonato
                $carreras->whereHas("campeonato_inscripcions", function ($query) use ($campeonato_id) {
                    $query->where("campeonato_id", $campeonato_id);
                });
            }
        }

        $carreras = $carreras->get();
        return $carreras;
    }
    /**
     * Lista de carreras paginado con filtros
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
        $carreras = Carrera::select("carreras.*");

        // Filtros exactos
        foreach ($columnsFilter as $key => $value) {
            if (!is_null($value)) {
                $carreras->where("carreras.$key", $value);
            }
        }

        // Filtros por rango
        foreach ($columnsBetweenFilter as $key => $value) {
            if (isset($value[0], $value[1])) {
                $carreras->whereBetween("carreras.$key", $value);
            }
        }

        // Búsqueda en múltiples columnas con LIKE
        if (!empty($search) && !empty($columnsSerachLike)) {
            $carreras->where(function ($query) use ($search, $columnsSerachLike) {
                foreach ($columnsSerachLike as $col) {
                    $query->orWhere("$col", "LIKE", "%$search%");
                }
            });
        }

        // Ordenamiento
        foreach ($orderBy as $value) {
            if (isset($value[0], $value[1])) {
                $carreras->orderBy($value[0], $value[1]);
            }
        }


        $carreras = $carreras->paginate($length, ['*'], 'page', $page);
        return $carreras;
    }

    /**
     * Crear carrera
     *
     * @param array $datos
     * @return Carrera
     */
    public function crear(array $datos): Carrera
    {
        $carrera = Carrera::create([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "descripcion" => mb_strtoupper($datos["descripcion"]) ?? NULL,
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UNA CARRERA", $carrera);

        return $carrera;
    }

    /**
     * Actualizar carrera
     *
     * @param array $datos
     * @param Carrera $carrera
     * @return Carrera
     */
    public function actualizar(array $datos, Carrera $carrera): Carrera
    {
        $old_carrera = clone $carrera;

        $carrera->update([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "descripcion" => mb_strtoupper($datos["descripcion"]) ?? NULL,
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UNA CARRERA", $old_carrera, $carrera->withoutRelations());

        return $carrera;
    }

    /**
     * Eliminar carrera
     *
     * @param Carrera $carrera
     * @return boolean
     */
    public function eliminar(Carrera $carrera): bool|Exception
    {
        $old_carrera = clone $carrera;
        $usos = CampeonatoInscripcion::where("carrera_id", $carrera->id)->count();
        if ($usos > 0) {
            throw new Exception("No se puede eliminar este tipo de documento porque está siendo utilizado por $usos productos.");
        }

        $carrera->delete();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UNA CARRERA", $old_carrera, $carrera);

        return true;
    }
}
