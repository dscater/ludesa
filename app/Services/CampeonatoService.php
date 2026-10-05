<?php

namespace App\Services;

use App\Models\CampeonatoInscripcion;
use App\Services\HistorialAccionService;
use App\Models\Campeonato;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CampeonatoService
{
    private $modulo = "CAMPEONATOS";

    public function __construct(
        private  CargarArchivoService $cargarArchivoService,
        private HistorialAccionService $historialAccionService,
        private CampeonatoInscripcionService $campeonato_inscripcion_service
    ) {}

    public function listado(): Collection
    {
        $campeonatos = Campeonato::select("campeonatos.*")
            ->orderBy("created_at", "desc")->get();
        return $campeonatos;
    }
    /**
     * Lista de campeonatos paginado con filtros
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
        $campeonatos = Campeonato::select("campeonatos.*");

        // Filtros exactos
        foreach ($columnsFilter as $key => $value) {
            if (!is_null($value)) {
                $campeonatos->where("campeonatos.$key", $value);
            }
        }

        // Filtros por rango
        foreach ($columnsBetweenFilter as $key => $value) {
            if (isset($value[0], $value[1])) {
                $campeonatos->whereBetween("campeonatos.$key", $value);
            }
        }

        // Búsqueda en múltiples columnas con LIKE
        if (!empty($search) && !empty($columnsSerachLike)) {
            $campeonatos->where(function ($query) use ($search, $columnsSerachLike) {
                foreach ($columnsSerachLike as $col) {
                    $query->orWhere("$col", "LIKE", "%$search%");
                }
            });
        }

        // Ordenamiento
        foreach ($orderBy as $value) {
            if (isset($value[0], $value[1])) {
                $campeonatos->orderBy($value[0], $value[1]);
            }
        }


        $campeonatos = $campeonatos->paginate($length, ['*'], 'page', $page);
        return $campeonatos;
    }

    /**
     * Crear campeonato
     *
     * @param array $datos
     * @return Campeonato
     */
    public function crear(array $datos): Campeonato
    {
        $campeonato = Campeonato::create([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "periodo" => $datos["periodo"],
            "gestion" => $datos["gestion"],
            "tipo" => mb_strtoupper($datos["tipo"]),
            "descripcion" => mb_strtoupper($datos["descripcion"]) ?? NULL,
            "fecha_registro" => date("Y-m-d"),
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN CAMPEONATO", $campeonato);

        return $campeonato;
    }

    /**
     * Actualizar campeonato
     *
     * @param array $datos
     * @param Campeonato $campeonato
     * @return Campeonato
     */
    public function actualizar(array $datos, Campeonato $campeonato): Campeonato
    {
        $old_campeonato = clone $campeonato;

        $campeonato->update([
            "nombre" => mb_strtoupper($datos["nombre"]),
            "periodo" => $datos["periodo"],
            "gestion" => $datos["gestion"],
            "tipo" => mb_strtoupper($datos["tipo"]),
            "descripcion" => mb_strtoupper($datos["descripcion"]) ?? NULL,
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN CAMPEONATO", $old_campeonato, $campeonato->withoutRelations());

        return $campeonato;
    }

    public function finalizar(Campeonato $campeonato): bool|Exception
    {
        $old_campeonato = clone $campeonato;

        $posicions = $this->campeonato_inscripcion_service->listadoPosicions($campeonato->id);

        $primero = $posicions[0];
        // Log::debug($primero->estado);
        if (!$primero) {
            throw new   Exception("No se pudo finalizar el campeonato porque no hay equipos inscritos");
        }

        $ganador = CampeonatoInscripcion::findOrFail($primero->id);
        $ganador->estado = "GANADOR";
        $ganador->save();

        $campeonato->estado = "FINALIZADO";
        $campeonato->save();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "FINALIZÓ UN CAMPEONATO", $old_campeonato, $campeonato);

        return true;
    }

    /**
     * Eliminar campeonato
     *
     * @param Campeonato $campeonato
     * @return boolean
     */
    public function eliminar(Campeonato $campeonato): bool|Exception
    {
        $old_campeonato = clone $campeonato;
        $usos = CampeonatoInscripcion::where("campeonato_id", $campeonato->id)->count();
        if ($usos > 0) {
            throw new Exception("No se puede eliminar este tipo de documento porque está siendo utilizado por $usos inscripciones.");
        }

        $campeonato->delete();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN CAMPEONATO", $old_campeonato, $campeonato);

        return true;
    }
}
