<?php

namespace App\Services;

use App\Models\CarreraJugadorInscripcion;
use App\Services\HistorialAccionService;
use App\Models\CarreraJugador;
use App\Models\PartidoDetalle;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CarreraJugadorService
{
    private $modulo = "CARRERA JUGADOR";

    public function __construct(private  CargarArchivoService $cargarArchivoService, private HistorialAccionService $historialAccionService) {}

    public function listado(
        $carrera_id = "",
        $campeonato_inscripcion_id = ""
    ): Collection {
        $carrera_jugadors = CarreraJugador::select("carrera_jugadors.*")
            ->with(["jugador:id,nombres,apes,ci"]);

        if ($carrera_id && $campeonato_inscripcion_id) {
            $carrera_jugadors->where("carrera_id", $carrera_id);
            $carrera_jugadors->where("campeonato_inscripcion_id", $campeonato_inscripcion_id);
        }

        $carrera_jugadors = $carrera_jugadors->get();
        return $carrera_jugadors;
    }
    /**
     * Lista de carrera_jugadors paginado con filtros
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
        $carrera_jugadors = CarreraJugador::select("carrera_jugadors.*")
            ->with(["jugador:id,nombres,apes,ci"]);


        // Búsqueda en múltiples columnas con LIKE
        if (!empty($search) && !empty($columnsSerachLike)) {
            $carrera_jugadors->where(function ($query) use ($search, $columnsSerachLike) {
                foreach ($columnsSerachLike as $col) {
                    $query->orWhere("$col", "LIKE", "%$search%");
                }
            });
        }

        if ($campeonato_id || $porCampeonato) {
            $carrera_jugadors->where("campeonato_id", $campeonato_id);
        }

        // Ordenamiento
        foreach ($orderBy as $value) {
            if (isset($value[0], $value[1])) {
                $carrera_jugadors->orderBy($value[0], $value[1]);
            }
        }


        $carrera_jugadors = $carrera_jugadors->paginate($length, ['*'], 'page', $page);
        return $carrera_jugadors;
    }

    /**
     * Crear carrera_jugador
     *
     * @param array $datos
     * @return CarreraJugador
     */
    public function crear(array $datos): CarreraJugador
    {
        $existe = CarreraJugador::where("campeonato_id", $datos["campeonato_id"])
            ->where("jugador_id", $datos["jugador_id"])
            ->get()->first();

        if ($existe) {
            throw new Exception("Este jugador ya fue registrado en el Campeonato");
        }

        $carrera_jugador = CarreraJugador::create([
            "campeonato_id" => $datos["campeonato_id"],
            "carrera_id" => $datos["carrera_id"],
            "campeonato_inscripcion_id" => $datos["campeonato_inscripcion_id"],
            "jugador_id" => $datos["jugador_id"],
            "posicion" => $datos["posicion"],
            "nro" => $datos["nro"],
            "fecha_registro" => date("Y-m-d"),
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN JUGADOR EN UNA CARRERA", $carrera_jugador);

        return $carrera_jugador;
    }

    /**
     * Actualizar carrera_jugador
     *
     * @param array $datos
     * @param CarreraJugador $carrera_jugador
     * @return CarreraJugador
     */
    public function actualizar(array $datos, CarreraJugador $carrera_jugador): CarreraJugador
    {
        $old_carrera_jugador = clone $carrera_jugador;
        $existe = CarreraJugador::where("campeonato_id", $datos["campeonato_id"])
            ->where("jugador_id", $datos["jugador_id"])
            ->where("id", "!=", $carrera_jugador->id)
            ->get()->first();

        if ($existe) {
            throw new Exception("Este jugador ya fue registrado en el Campeonato");
        }

        $carrera_jugador->update([
            "campeonato_id" => $datos["campeonato_id"],
            "carrera_id" => $datos["carrera_id"],
            "campeonato_inscripcion_id" => $datos["campeonato_inscripcion_id"],
            "jugador_id" => $datos["jugador_id"],
            "posicion" => $datos["posicion"],
            "nro" => $datos["nro"],
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN JUGADOR EN UNA CARRERA", $old_carrera_jugador, $carrera_jugador->withoutRelations());

        return $carrera_jugador;
    }

    /**
     * Eliminar carrera_jugador
     *
     * @param CarreraJugador $carrera_jugador
     * @return boolean
     */
    public function eliminar(CarreraJugador $carrera_jugador): bool|Exception
    {
        $old_carrera_jugador = clone $carrera_jugador;
        $usos = PartidoDetalle::where("carrera_jugador_id", $carrera_jugador->id)->count();
        if ($usos > 0) {
            throw new Exception("No se puede eliminar este tipo de documento porque está siendo utilizado por $usos registros de partidos.");
        }
        $carrera_jugador->delete();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN JUGADOR EN UNA CARRERA", $old_carrera_jugador, $carrera_jugador);

        return true;
    }
}
