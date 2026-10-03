<?php

namespace App\Services;

use App\Models\CampeonatoInscripcionInscripcion;
use App\Services\HistorialAccionService;
use App\Models\CampeonatoInscripcion;
use App\Models\CarreraJugador;
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

class CampeonatoInscripcionService
{
    private $modulo = "CAMPEONATO INSCRIPCION";

    public function __construct(private  CargarArchivoService $cargarArchivoService, private HistorialAccionService $historialAccionService) {}

    public function listado($campeonato_id = null, $jugadores = true): Collection
    {

        $relaciones = [
            "campeonato:id,periodo,gestion,nombre,tipo",
            "carrera:id,nombre",
            "carrera_jugadors"
        ];

        if (!$jugadores)
            $relaciones = [
                "campeonato:id,periodo,gestion,nombre,tipo",
                "carrera:id,nombre",
            ];


        $campeonato_inscripcions = CampeonatoInscripcion::select("campeonato_inscripcions.*")
            ->with($relaciones);

        if ($campeonato_id) {
            $campeonato_inscripcions->where("campeonato_id", $campeonato_id);
        }

        $campeonato_inscripcions = $campeonato_inscripcions->get();
        return $campeonato_inscripcions;
    }

    public function listadoPosicions($campeonato_id = null, $jugadores = true): Collection
    {
        $relaciones = [
            "campeonato:id,periodo,gestion,nombre,tipo",
            "carrera",
            "carrera_jugadors"
        ];

        if (!$jugadores)
            $relaciones = [
                "campeonato:id,periodo,gestion,nombre,tipo",
                "carrera",
            ];


        $campeonato_inscripcions = CampeonatoInscripcion::select("campeonato_inscripcions.*")
            ->with($relaciones);

        if ($campeonato_id) {
            $campeonato_inscripcions->where("campeonato_id", $campeonato_id);
        }

        $campeonato_inscripcions = $campeonato_inscripcions
            ->orderBy("pts", "desc")
            ->orderBy("gf", "desc")
            ->get();

        // Enumerar posiciones
        $campeonato_inscripcions->each(function ($item, $index) {
            $item->posicion = $index + 1;
        });

        return $campeonato_inscripcions;
    }

    public function listadoGoleadores($campeonato_id = null): Collection
    {
        $relaciones = [
            "campeonato:id,periodo,gestion,nombre,tipo",
            "carrera",
            "jugador"
        ];

        $carrera_jugadors = CarreraJugador::with($relaciones);

        if ($campeonato_id) {
            $carrera_jugadors
                ->where("campeonato_id", $campeonato_id)
                ->withSum([
                    "partido_detalles as goles" => function ($query) use ($campeonato_id) {
                        $query->whereHas("partido", function ($q) use ($campeonato_id) {
                            $q->where("campeonato_id", $campeonato_id);
                        });
                    }
                ], "goles")
                ->orderByDesc("goles");
        }

        $carrera_jugadors = $carrera_jugadors->get();

        $carrera_jugadors->each(function ($item, $index) {
            $item->posicion = $index + 1;
        });

        return $carrera_jugadors;
    }

    public function listadoPorteros($campeonato_id = null): Collection
    {
        $relaciones = [
            "campeonato:id,periodo,gestion,nombre,tipo",
            "carrera",
            "jugador",
            "partido_detalles.partido",
        ];

        $carrera_jugadors = CarreraJugador::select("carrera_jugadors.*")
            ->with($relaciones)
            ->where("posicion", "PORTERO")
            ->whereHas("partido_detalles", function ($query) use ($campeonato_id) {

                // Debe haber sido titular al menos una vez
                $query->where("titular", 1);

                // El partido debe pertenecer al campeonato
                if ($campeonato_id) {
                    $query->whereHas("partido", function ($q) use ($campeonato_id) {
                        $q->where("campeonato_id", $campeonato_id);
                    });
                }
            });

        if ($campeonato_id) {
            $carrera_jugadors->where("campeonato_id", $campeonato_id);
        }

        $porteros = $carrera_jugadors->get();

        /*
     * Calcular goles recibidos
     */
        $porteros->each(function ($portero) {

            $goles_recibidos = 0;
            $partidos_titular = 0;

            foreach ($portero->partido_detalles as $detalle) {

                // Solo considerar partidos donde fue titular
                if ($detalle->titular != 1) {
                    continue;
                }

                $partido = $detalle->partido;

                if (!$partido) {
                    continue;
                }

                $partidos_titular++;

                /*
             * El portero pertenece al equipo LOCAL,
             * por lo tanto recibe los goles del VISITANTE.
             */
                if ($partido->ci_local_id == $portero->campeonato_inscripcion_id) {

                    $goles_recibidos += (int) $partido->goles_visitante;
                }

                /*
             * El portero pertenece al equipo VISITANTE,
             * por lo tanto recibe los goles del LOCAL.
             */ elseif ($partido->ci_visitante_id == $portero->campeonato_inscripcion_id) {

                    $goles_recibidos += (int) $partido->goles_local;
                }
            }

            $portero->partidos_titular = $partidos_titular;
            $portero->goles_recibidos = $goles_recibidos;
        });

        /*
     * Ordenar de menor a mayor cantidad de goles recibidos
     */
        $porteros = $porteros
            ->sortBy("goles_recibidos")
            ->values();

        /*
     * Enumerar ranking sin tocar la columna "posicion"
     */
        $porteros->each(function ($item, $index) {
            $item->ranking = $index + 1;
        });

        return $porteros;
    }

    public function deudas($campeonato_inscripcion, $partido_id = null)
    {
        $partidos_local = Partido::with(["partido_detalles.carrera_jugador.jugador"])
            ->where("ci_local_id", $campeonato_inscripcion->id)
            ->when($partido_id, function ($query) use ($partido_id) {
                $query->where("id", "!=", $partido_id);
            })
            ->where(function ($query) {
                $query->where("pago_local", 0)
                    ->orWhereHas("partido_detalles", function ($query) {
                        $query->where(function ($q) {
                            $q->where("amarillas", ">", 0)
                                ->where("pagado_amarillas", 0);
                        });
                        $query->orWhere(function ($q) {
                            $q->where("rojas", ">", 0)
                                ->where("pagado_rojas", 0);
                        });
                    });
            })
            ->get();

        $partidos_visitante = Partido::with([
            "partido_detalles.carrera_jugador.jugador"
        ])
            ->where("ci_visitante_id", $campeonato_inscripcion->id)
            ->when($partido_id, function ($query) use ($partido_id) {
                $query->where("id", "!=", $partido_id);
            })
            ->where(function ($query) {
                $query->where("pago_visitante", 0)
                    ->orWhereHas("partido_detalles", function ($query) {
                        $query->where(function ($q) {
                            $q->where("amarillas", ">", 0)
                                ->where("pagado_amarillas", 0);
                        });
                        $query->orWhere(function ($q) {
                            $q->where("rojas", ">", 0)
                                ->where("pagado_rojas", 0);
                        });
                    });
            })
            ->get();

        $deudas = ["local" => $partidos_local, "visitante" => $partidos_visitante];

        return $deudas;
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
        $carrera_id,
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

        if ($carrera_id) {
            $campeonato_inscripcions->where("carrera_id", $carrera_id);
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

    public function listadoPaginadoPagos(
        int $length,
        int $page,
        string $search,
        $campeonato_id,
        $carrera_id,
        $fecha_ini,
        $fecha_fin,
        $porCampeonato = true,
        array $orderBy = []
    ): LengthAwarePaginator {
        $campeonato_inscripcions = CampeonatoInscripcion::select("campeonato_inscripcions.*")
            ->with([
                "campeonato:id,periodo,gestion,nombre,tipo",
                "carrera:id,nombre",
                "carrera_jugadors",
            ])
            ->where(function ($query) use ($fecha_ini, $fecha_fin) {

                // PARTIDOS COMO LOCAL
                $query->whereHas('partidos_local', function ($q) use ($fecha_ini, $fecha_fin) {

                    $q->where('pago_local', 0);

                    if ($fecha_ini) {
                        $q->whereDate('fecha', '>=', $fecha_ini);
                    }

                    if ($fecha_fin) {
                        $q->whereDate('fecha', '<=', $fecha_fin);
                    }
                })

                    // PARTIDOS COMO VISITANTE
                    ->orWhereHas('partidos_visitante', function ($q) use ($fecha_ini, $fecha_fin) {

                        $q->where('pago_visitante', 0);

                        if ($fecha_ini) {
                            $q->whereDate('fecha', '>=', $fecha_ini);
                        }

                        if ($fecha_fin) {
                            $q->whereDate('fecha', '<=', $fecha_fin);
                        }
                    })

                    // TARJETAS
                    ->orWhereHas('partido_detalles', function ($q) use ($fecha_ini, $fecha_fin) {

                        $q->where(function ($q) {
                            $q->where(function ($q) {
                                $q->where('amarillas', '>', 0)
                                    ->where('pagado_amarillas', 0);
                            })
                                ->orWhere(function ($q) {
                                    $q->where('rojas', '>', 0)
                                        ->where('pagado_rojas', 0);
                                });
                        });

                        if ($fecha_ini) {
                            $q->whereHas('partido', function ($q) use ($fecha_ini) {
                                $q->whereDate('fecha', '>=', $fecha_ini);
                            });
                        }

                        if ($fecha_fin) {
                            $q->whereHas('partido', function ($q) use ($fecha_fin) {
                                $q->whereDate('fecha', '<=', $fecha_fin);
                            });
                        }
                    })
                    // INSCRIPCION
                    ->orWhere("pago_inscripcion", 0);
            })
            ->when($carrera_id, function ($query) use ($carrera_id) {
                $query->where("carrera_id", $carrera_id);
            });

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

        $campeonato_inscripcions->setCollection(
            $campeonato_inscripcions->getCollection()->map(function ($inscripcion) {
                $deuda_local = $inscripcion->partidos_local->where("pago_local", 0)
                    ->sum("total_local");
                $deuda_visitante = $inscripcion->partidos_visitante->where("pago_visitante", 0)
                    ->sum("total_visitante");

                $deuda_partidos = $deuda_local + $deuda_visitante;
                $deuda_amarillas = $inscripcion->partido_detalles->where("pagado_amarillas", 0)->sum("total_amarillas");
                $deuda_rojas = $inscripcion->partido_detalles->where("pagado_rojas", 0)->sum("total_rojas");

                $inscripcion->deuda_partidos = $deuda_partidos;
                $inscripcion->deuda_amarillas = $deuda_amarillas;
                $inscripcion->deuda_rojas = $deuda_rojas;

                return $inscripcion;
            })
        );
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
            "total_inscripcion" => $datos["total_inscripcion"],
            "pago_inscripcion" => $datos["pago_inscripcion"],
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
            "total_inscripcion" => $datos["total_inscripcion"],
            "pago_inscripcion" => $datos["pago_inscripcion"],
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

    public function actualizaPago(CampeonatoInscripcion $campeonato_inscripcion, $pago_inscripcion)
    {
        $old_campeonato_inscripcion = clone $campeonato_inscripcion;
        $campeonato_inscripcion->pago_inscripcion = $pago_inscripcion;
        $campeonato_inscripcion->save();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ EL PAGO DE INSCRIPCIÓN DE UNA CARRERA EN UN CAMPEONATO", $old_campeonato_inscripcion, $campeonato_inscripcion->withoutRelations());
    }

    public function actualizaPartidoJugado(
        CampeonatoInscripcion $campeonato_inscripcion,
        $resultado = "empate",
        $goles,
        $goles_recibidos,
    ) {
        $campeonato_inscripcion->pj = $campeonato_inscripcion->pj  + 1;
        if ($resultado == 'ganador') {
            $campeonato_inscripcion->pg = $campeonato_inscripcion->pg + 1;
            $campeonato_inscripcion->pts = $campeonato_inscripcion->pts + 3;
        }

        if ($resultado == 'empate') {
            $campeonato_inscripcion->pe = $campeonato_inscripcion->pe + 1;
            $campeonato_inscripcion->pts = $campeonato_inscripcion->pts + 1;
        }

        if ($resultado == 'perdedor') {
            $campeonato_inscripcion->pp = $campeonato_inscripcion->pp + 1;
        }
        $campeonato_inscripcion->gf = $campeonato_inscripcion->gf + $goles;
        $campeonato_inscripcion->gc = $campeonato_inscripcion->gc + $goles_recibidos;
        $campeonato_inscripcion->dg = $campeonato_inscripcion->gf - $campeonato_inscripcion->gc;

        $campeonato_inscripcion->save();
    }
}
