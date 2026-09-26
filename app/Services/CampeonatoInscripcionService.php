<?php

namespace App\Services;

use App\Models\CampeonatoInscripcionInscripcion;
use App\Services\HistorialAccionService;
use App\Models\CampeonatoInscripcion;
use App\Models\CarreraJugador;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CampeonatoInscripcionService
{
    private $modulo = "CAMPEONATO INSCRIPCION";

    public function __construct(private  CargarArchivoService $cargarArchivoService, private HistorialAccionService $historialAccionService) {}

    public function listado(): Collection
    {
        $campeonato_inscripcions = CampeonatoInscripcion::select("campeonato_inscripcions.*")
            ->with([
                "campeonato:id,periodo,gestion,nombre,tipo",
                "carrera:id,nombre",
                "carrera_jugadors.*"
            ])
            ->get();
        return $campeonato_inscripcions;
    }
    /**
     * Lista de campeonato_inscripcions paginado con filtros
     *
     * @param integer $length
     * @param integer $page
     * @param string $search
     * @param $campeonato_id
     * @return LengthAwarePaginator
     */
    public function listadoPaginado(
        int $length,
        int $page,
        string $search,
        $campeonato_id,
        $porCampeonato = true,
        array $orderBy = []
    ): LengthAwarePaginator {
        $campeonato_inscripcions = CampeonatoInscripcion::select("campeonato_inscripcions.*")
            ->with([
                "campeonato:id,periodo,gestion,nombre,tipo",
                "carrera:id,nombre",
                "carrera_jugadors"
            ]);

        // Búsqueda en múltiples columnas con LIKE
        if (!empty($search) && !empty($columnsSerachLike)) {
            $campeonato_inscripcions->where(function ($query) use ($search, $columnsSerachLike) {
                foreach ($columnsSerachLike as $col) {
                    $query->orWhere("$col", "LIKE", "%$search%");
                }
            });
        }

        if ($campeonato_id || $porCampeonato) {
            $campeonato_inscripcions->where("campeonato_id", $campeonato_id);
        }

        // Ordenamiento
        foreach ($orderBy as $value) {
            if (isset($value[0], $value[1])) {
                $campeonato_inscripcions->orderBy($value[0], $value[1]);
            }
        }


        $campeonato_inscripcions = $campeonato_inscripcions->paginate($length, ['*'], 'page', $page);
        return $campeonato_inscripcions;
    }

    /**
     * Crear campeonato_inscripcion
     *
     * @param array $datos
     * @return CampeonatoInscripcion
     */
    public function crear(array $datos): CampeonatoInscripcion
    {
        $existe = CampeonatoInscripcion::where("campeonato_id", $datos["campeonato_id"])
            ->where("carrera_id", $datos["carrera_id"])->get()->first();

        if ($existe) {
            throw new Exception("Esta carrera ya fue registrada en el Campeonato");
        }

        $campeonato_inscripcion = CampeonatoInscripcion::create([
            "campeonato_id" => $datos["campeonato_id"],
            "carrera_id" => $datos["carrera_id"],
            "fecha" => $datos["fecha"],
            "hora" => $datos["hora"],
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UNA CARRERA EN UN CAMPEONATO", $campeonato_inscripcion);

        return $campeonato_inscripcion;
    }

    /**
     * Actualizar campeonato_inscripcion
     *
     * @param array $datos
     * @param CampeonatoInscripcion $campeonato_inscripcion
     * @return CampeonatoInscripcion
     */
    public function actualizar(array $datos, CampeonatoInscripcion $campeonato_inscripcion): CampeonatoInscripcion
    {
        $old_campeonato_inscripcion = clone $campeonato_inscripcion;
        $existe = CampeonatoInscripcion::where("campeonato_id", $datos["campeonato_id"])
            ->where("carrera_id", $datos["carrera_id"])
            ->where("id", "!=", $campeonato_inscripcion->id)
            ->get()->first();

        if ($existe) {
            throw new Exception("Esta carrera ya fue registrada en el Campeonato");
        }

        if ($old_campeonato_inscripcion->carrera_id != $datos["carrera_id"]) {
            // ACTUALIZAR CARRERA JUGADORS SI SE CAMBIO LA CARRERA
            $carrera_jugadors = CarreraJugador::where("campeonato_inscripcion_id", $campeonato_inscripcion->id)
                ->get();
            foreach ($carrera_jugadors as $item) {
                $item->carrera_id = $datos["carrera_id"];
                $item->save();
            }
        }

        $campeonato_inscripcion->update([
            "campeonato_id" => $datos["campeonato_id"],
            "carrera_id" => $datos["carrera_id"],
            "fecha" => $datos["fecha"],
            "hora" => $datos["hora"],
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UNA CARRERA EN UN CAMPEONATO", $old_campeonato_inscripcion, $campeonato_inscripcion->withoutRelations());

        return $campeonato_inscripcion;
    }

    /**
     * Eliminar campeonato_inscripcion
     *
     * @param CampeonatoInscripcion $campeonato_inscripcion
     * @return boolean
     */
    public function eliminar(CampeonatoInscripcion $campeonato_inscripcion): bool|Exception
    {
        $old_campeonato_inscripcion = clone $campeonato_inscripcion;
        $usos = CarreraJugador::where("campeonato_inscripcion_id", $campeonato_inscripcion->id)->count();
        if ($usos > 0) {
            throw new Exception("No se puede eliminar este tipo de documento porque está siendo utilizado por $usos registros de inscripción de jugadores.");
        }

        $campeonato_inscripcion->delete();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UNA CARRERA EN UN CAMPEONATO", $old_campeonato_inscripcion, $campeonato_inscripcion);

        return true;
    }
}
