<?php

namespace App\Services;

use App\Models\CarreraJugador;
use App\Models\IngresoDetalle;
use App\Services\HistorialAccionService;
use App\Models\Jugador;
use App\Models\SalidaJugador;
use App\Models\VentaDetalle;
use App\Models\VentaDetalleLote;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class JugadorService
{
    private $modulo = "JUGADORES";

    public function __construct(private  CargarArchivoService $cargarArchivoService, private HistorialAccionService $historialAccionService) {}

    public function listado($activo = null): Collection
    {
        $jugadors = Jugador::select("jugadors.*");
        $jugadors = $jugadors->get();
        return $jugadors;
    }
    /**
     * Lista de jugadors paginado con filtros
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
        $jugadors = Jugador::select("jugadors.*");

        // Filtros exactos
        foreach ($columnsFilter as $key => $value) {
            if (!is_null($value)) {
                $jugadors->where("jugadors.$key", $value);
            }
        }

        // Filtros por rango
        foreach ($columnsBetweenFilter as $key => $value) {
            if (isset($value[0], $value[1])) {
                $jugadors->whereBetween("jugadors.$key", $value);
            }
        }

        // Búsqueda en múltiples columnas con LIKE
        if (!empty($search) && !empty($columnsSerachLike)) {
            $jugadors->where(function ($query) use ($search, $columnsSerachLike) {
                foreach ($columnsSerachLike as $col) {
                    $query->orWhere("$col", "LIKE", "%$search%");
                }
            });
        }

        // Ordenamiento
        foreach ($orderBy as $value) {
            if (isset($value[0], $value[1])) {
                $jugadors->orderBy($value[0], $value[1]);
            }
        }


        $jugadors = $jugadors->paginate($length, ['*'], 'page', $page);
        return $jugadors;
    }

    /**
     * Crear jugador
     *
     * @param array $datos
     * @return Jugador
     */
    public function crear(array $datos): Jugador
    {
        $jugador = Jugador::create([
            "nombres" => mb_strtoupper($datos["nombres"]),
            "apes" => mb_strtoupper($datos["apes"]),
            "ci" => $datos["ci"],
            "correo" => $datos["correo"],
            "dir" => mb_strtoupper($datos["dir"] ?? ''),
            "fono" => $datos["fono"],
            "fecha_registro" => date("Y-m-d")
        ]);

        // cargar foto
        if (isset($datos["foto"]) && !is_string($datos["foto"])) {
            $this->cargarImagen($jugador, $datos["foto"]);
        }

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN JUGADOR", $jugador);

        return $jugador;
    }

    /**
     * Actualizar jugador
     *
     * @param array $datos
     * @param Jugador $jugador
     * @return Jugador
     */
    public function actualizar(array $datos, Jugador $jugador): Jugador
    {
        $old_jugador = clone $jugador;

        $jugador->update([
            "nombres" => mb_strtoupper($datos["nombres"]),
            "apes" => mb_strtoupper($datos["apes"]),
            "ci" => $datos["ci"],
            "correo" => $datos["correo"],
            "dir" => mb_strtoupper($datos["dir"] ?? ''),
            "fono" => $datos["fono"],
        ]);

        // cargar foto
        if (isset($datos["foto"]) && !is_string($datos["foto"])) {
            $this->cargarImagen($jugador, $datos["foto"]);
        }

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN JUGADOR", $old_jugador, $jugador->withoutRelations());

        return $jugador;
    }

    /**
     * Cargar foto
     *
     * @param Jugador $jugador
     * @param UploadedFile $foto
     * @return void
     */
    public function cargarImagen(Jugador $jugador, UploadedFile $foto): void
    {
        if ($jugador->foto) {
            \File::delete(public_path("imgs/jugadors/" . $jugador->foto));
        }
        $nombre = $jugador->id . time();
        $jugador->foto = $this->cargarArchivoService->cargarArchivo($foto, public_path("imgs/jugadors"), $nombre);
        $jugador->save();
    }

    /**
     * Eliminar jugador
     *
     * @param Jugador $jugador
     * @return boolean
     */
    public function eliminar(Jugador $jugador): bool|Exception
    {
        $old_jugador = clone $jugador;
        $usos = CarreraJugador::where("jugador_id", $jugador->id)->count();
        if ($usos > 0) {
            throw new Exception("No se puede eliminar este jugador porque está siendo utilizado por $usos registros de Inscripciones en Carreras.");
        }
        $jugador->delete();
        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN JUGADOR", $old_jugador, $jugador);

        return true;
    }
}
