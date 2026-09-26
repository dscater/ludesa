<?php

namespace App\Services;

use App\Models\CampeonatoInscripcion;
use App\Models\Carrera;
use App\Models\PartidoInscripcion;
use App\Services\HistorialAccionService;
use App\Models\Partido;
use App\Models\PartidoDetalle;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class PartidoService
{
    private $modulo = "CARRERA JUGADOR";

    public function __construct(private  CargarArchivoService $cargarArchivoService, private HistorialAccionService $historialAccionService) {}

    public function listado(
        $campeonato_id = ""
    ): Collection {
        $partidos = Partido::select("partidos.*")
            ->with(["ci_local.carrera", "ci_visitante.carrera"]);

        if ($campeonato_id) {
            $partidos->where("campeonato_id", $campeonato_id);
        }

        $partidos = $partidos->get();
        return $partidos;
    }
    /**
     * Lista de partidos paginado con filtros
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

        $partidos = Partido::select("partidos.*")
            ->with(["ci_local.carrera", "ci_visitante.carrera"]);

        // Búsqueda en múltiples columnas con LIKE
        if (!empty($search) && !empty($columnsSerachLike)) {
            $partidos->where(function ($query) use ($search, $columnsSerachLike) {
                foreach ($columnsSerachLike as $col) {
                    $query->orWhere("$col", "LIKE", "%$search%");
                }
            });
        }

        if ($campeonato_id || $porCampeonato) {
            $partidos->where("campeonato_id", $campeonato_id);
        }

        // Ordenamiento
        foreach ($orderBy as $value) {
            if (isset($value[0], $value[1])) {
                $partidos->orderBy($value[0], $value[1]);
            }
        }


        $partidos = $partidos->paginate($length, ['*'], 'page', $page);
        return $partidos;
    }

    /**
     * Crear partido
     *
     * @param array $datos
     * @return Partido
     */
    public function crear(array $datos): Partido
    {
        if ($datos["ci_local_id"] == $datos["ci_visitante_id"]) {
            throw new Exception("No puedes seleccionar el mismo equipo para el partido");
        }

        $ci_local = CampeonatoInscripcion::findOrFail($datos["ci_local_id"]);

        $ci_visitante = CampeonatoInscripcion::findOrFail($datos["ci_local_id"]);
        $partido = Partido::create([
            "campeonato_id" => $datos["campeonato_id"],
            "ci_local_id" => $datos["ci_local_id"],
            "local_id" => $ci_local->carrera_id,
            "ci_visitante_id" => $datos["ci_visitante_id"],
            "visitante_id" => $ci_visitante->carrera_id,
            "total_local" => 0,
            "pago_local" => 0, // NO PAGADO
            "total_visitante" => 0,
            "pago_visitante" => 0, // NO PAGADO,
            "fecha" => $datos["fecha"],
            "hora" => $datos["hora"],
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN PARTIDO", $partido);

        return $partido;
    }

    /**
     * Actualizar partido
     *
     * @param array $datos
     * @param Partido $partido
     * @return Partido
     */
    public function actualizar(array $datos, Partido $partido): Partido
    {
        $old_partido = clone $partido;
        if ($datos["ci_local_id"] == $datos["ci_visitante_id"]) {
            throw new Exception("No puedes seleccionar el mismo equipo para el partido");
        }

        $ci_local = CampeonatoInscripcion::findOrFail($datos["ci_local_id"]);

        $ci_visitante = CampeonatoInscripcion::findOrFail($datos["ci_local_id"]);

        $partido->update([
            "campeonato_id" => $datos["campeonato_id"],
            "ci_local_id" => $datos["ci_local_id"],
            "local_id" => $ci_local->carrera_id,
            "ci_visitante_id" => $datos["ci_visitante_id"],
            "visitante_id" => $ci_visitante->carrera_id,
            "fecha" => $datos["fecha"],
            "hora" => $datos["hora"],
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN PARTIDO", $old_partido, $partido->withoutRelations());

        return $partido;
    }

    public function iniciarPartido(Partido $partido): Partido
    {
        $old_partido = clone $partido;

        $ci_local = CampeonatoInscripcion::findOrFail($partido->ci_local_id);
        $ci_visitante = CampeonatoInscripcion::findOrFail($partido->ci_visitante_id);

        // LOCAL
        foreach ($ci_local->carrera_jugadors as $item) {
            $existe = PartidoDetalle::where("campeonato_id", $partido->campeonato_id)
                ->where("campeonato_inscripcion_id", $ci_local->id)
                ->where("carrera_id", $ci_local->carrera_id)
                ->where("carrera_jugador_id", $item->id)
                ->get()->first();
            if (!$existe)
                $partido->partido_detalles()->create([
                    "campeonato_id" => $partido->campeonato_id,
                    "campeonato_inscripcion_id" => $ci_local->id,
                    "carrera_id" => $ci_local->carrera_id,
                    "carrera_jugador_id" => $item->id,
                ]);
        }

        // VISITANTE
        foreach ($ci_visitante->carrera_jugadors as $item) {
            $existe = PartidoDetalle::where("campeonato_id", $partido->campeonato_id)
                ->where("campeonato_inscripcion_id", $ci_visitante->id)
                ->where("carrera_id", $ci_visitante->carrera_id)
                ->where("carrera_jugador_id", $item->id)
                ->get()->first();
            if (!$existe)
                $partido->partido_detalles()->create([
                    "campeonato_id" => $partido->campeonato_id,
                    "campeonato_inscripcion_id" => $ci_visitante->id,
                    "carrera_id" => $ci_visitante->carrera_id,
                    "carrera_jugador_id" => $item->id,
                ]);
        }

        $partido->estado = 'INICIADO';
        $partido->save();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "INICIO UN PARTIDO", $old_partido, $partido->withoutRelations(), ["partido_detalles"]);

        return $partido;
    }

    /**
     * Eliminar partido
     *
     * @param Partido $partido
     * @return boolean
     */
    public function eliminar(Partido $partido): bool|Exception
    {
        $old_partido = clone $partido;
        $usos = PartidoDetalle::where("partido_id", $partido->id)->count();
        if ($usos > 0) {
            throw new Exception("No se puede eliminar este tipo de documento porque está siendo utilizado por $usos registros de partidos.");
        }
        $partido->delete();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN PARTIDO", $old_partido, $partido);

        return true;
    }
}
