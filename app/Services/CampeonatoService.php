<?php

namespace App\Services;

use App\Models\CampeonatoInscripcion;
use App\Services\HistorialAccionService;
use App\Models\Campeonato;
use App\Models\Partido;
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

    public function generar_fechas(Campeonato $campeonato)
    {
        $old_campeonato = clone $campeonato;

        // Round Robin mediante rotación circular (Circle Method)
        //1)Se colocan los equipos en una lista.
        //2)Se emparejan los equipos de los extremos: el primero con el último, el segundo con el penúltimo, y así sucesivamente.
        //3)Se mantiene fijo el primer equipo y se rotan los demás para generar la siguiente jornada.
        //4)Si hay un número impar de equipos, se agrega un equipo ficticio que representa el descanso.
        //5)Para la segunda vuelta, se invierte la localía de cada enfrentamiento.
        //6)Obtener los equipos inscritos
        $inscripciones = $campeonato
            ->campeonato_inscripcions()
            ->get();

        if ($inscripciones->count() < 2) {
            throw ValidationException::withMessages([
                'campeonato' =>
                'Se necesitan al menos 2 equipos inscritos.',
            ]);
        }

        // Evitar generar partidos si ya éxisten
        if (
            Partido::where('campeonato_id', $campeonato->id)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'campeonato' =>
                'El campeonato ya tiene partidos generados.',
            ]);
        }

        // Cada elemento conserva el ID de inscripción
        // y el ID del equipo.
        $equipos = $inscripciones->map(fn($inscripcion) => [
            'ci_id' => $inscripcion->id,
            'carrera_id' => $inscripcion->carrera_id,
        ])->values()->all();

        $cantidad = count($equipos);

        // Si es impar, agregar un descanso.
        if ($cantidad % 2 !== 0) {
            $equipos[] = null;
        }

        $cantidadPorFecha = count($equipos);
        $totalFechasPrimeraVuelta = $cantidadPorFecha - 1;
        $partidosPorFecha = intdiv($cantidadPorFecha, 2);

        $fechasPrimeraVuelta = [];

        // Generar primera vuelta
        for ($jornada = 0; $jornada < $totalFechasPrimeraVuelta; $jornada++) {

            $partidosFecha = [];

            for ($i = 0; $i < $partidosPorFecha; $i++) {

                $equipoA = $equipos[$i];
                $equipoB = $equipos[$cantidadPorFecha - 1 - $i];

                // Si alguno descansa, no se crea partido.
                if ($equipoA === null || $equipoB === null) {
                    continue;
                }

                // Alternar localía para equilibrar
                // la condición de local y visitante.
                if (($jornada + $i) % 2 === 0) {
                    $local = $equipoA;
                    $visitante = $equipoB;
                } else {
                    $local = $equipoB;
                    $visitante = $equipoA;
                }

                $partidosFecha[] = [
                    'local' => $local,
                    'visitante' => $visitante,
                ];
            }

            $fechasPrimeraVuelta[] = $partidosFecha;

            // Rotación circular: mantener fijo el primer equipo.
            $ultimo = array_pop($equipos);

            array_splice($equipos, 1, 0, [$ultimo]);
        }

        // Generar ida y vuelta
        $todasLasFechas = $fechasPrimeraVuelta;

        foreach ($fechasPrimeraVuelta as $partidosFecha) {
            $partidosVuelta = [];

            foreach ($partidosFecha as $partido) {
                // Invertir localía en la segunda vuelta.
                $partidosVuelta[] = [
                    'local' => $partido['visitante'],
                    'visitante' => $partido['local'],
                ];
            }

            $todasLasFechas[] = $partidosVuelta;
        }

        // Registrar todos los partidos.
        foreach ($todasLasFechas as $indiceFecha => $partidosFecha) {

            $nroFecha = $indiceFecha + 1;

            foreach ($partidosFecha as $partido) {

                Partido::create([
                    'campeonato_id' => $campeonato->id,

                    'ci_local_id' => $partido['local']['ci_id'],
                    'local_id' => $partido['local']['carrera_id'],

                    'ci_visitante_id' => $partido['visitante']['ci_id'],
                    'visitante_id' => $partido['visitante']['carrera_id'],

                    'nro_fecha' => $nroFecha,
                    'fecha_asignada' => 0,

                    'goles_local' => 0,
                    'goles_visitante' => 0,

                    'ganador_id' => null,
                    'ci_ganador_id' => null,

                    'total_local' => 0,
                    'pago_local' => 0,
                    'total_visitante' => 0,
                    'pago_visitante' => 0,

                    'fecha' => null,
                    'hora' => null,
                    'estado' => 'PENDIENTE',
                ]);
            }
        }

        $campeonato->inicio_fechas = 1;
        $campeonato->save();

        // Registrar acción en el historial
        $this->historialAccionService->registrarAccion(
            $this->modulo,
            'MODIFICACIÓN',
            'GENERÓ LAS FECHAS DE UN CAMPEONATO',
            $old_campeonato,
            $campeonato->withoutRelations()
        );
    }

    public function getFechas(Campeonato $campeonato)
    {
        return Partido::where("campeonato_id", $campeonato->id)
            ->select("nro_fecha")
            ->distinct()
            ->orderBy("nro_fecha", "desc")
            ->pluck("nro_fecha");
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
