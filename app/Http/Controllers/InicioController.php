<?php

namespace App\Http\Controllers;

use App\Models\CampeonatoInscripcion;
use App\Models\Certificado;
use App\Models\CertificadoDetalle;
use App\Models\Partido;
use App\Models\PartidoDetalle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InicioController extends Controller
{

    public function verificaLogin()
    {
        $sw = false;
        if (Auth::check()) {
            $sw = true;
        }

        return response()->JSON(["sw" => $sw]);
    }

    public function inicio()
    {
        $array_infos = UserController::getInfoBoxUser();

        return Inertia::render('Admin/Home', compact('array_infos'));
    }

    public function login()
    {
        return Inertia::render("Auth/Login");
    }

    public function pagosCampeonato(Request $request)
    {
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $campeonato_id = $request->campeonato_id;
        $carrera_id = $request->carrera_id;

        /*
    |--------------------------------------------------------------------------
    | INSCRIPCIONES
    |--------------------------------------------------------------------------
    */

        $inscripcionesQuery = CampeonatoInscripcion::query();

        $inscripcionesQuery->when(
            $campeonato_id && $campeonato_id != 'todos',
            function ($query) use ($campeonato_id) {
                $query->where('campeonato_id', $campeonato_id);
            }
        );

        // Filtro por carrera
        $inscripcionesQuery->when(
            $carrera_id && $carrera_id != "todos",
            function ($q) use ($carrera_id) {
                $q->where('carrera_id', $carrera_id);
            }
        );

        // Filtro por fechas
        $inscripcionesQuery->when(
            $fecha_ini && $fecha_fin,
            function ($q) use ($fecha_ini, $fecha_fin) {
                $q->whereBetween('fecha', [$fecha_ini, $fecha_fin]);
            }
        );

        $inscripciones = $inscripcionesQuery->get();

        $inscripcionesPendientes = $inscripciones
            ->where('pago_inscripcion', 0)
            ->sum('total_inscripcion');

        $inscripcionesTotal = $inscripciones
            ->sum('total_inscripcion');

        $inscripcionesCanceladas =
            $inscripcionesTotal - $inscripcionesPendientes;


        /*
    |--------------------------------------------------------------------------
    | DERECHOS DE CANCHA
    |--------------------------------------------------------------------------
    */

        $partidosQuery = Partido::query();

        /*
     * El partido debe tener al menos una inscripción que cumpla
     * con los filtros de campeonato y carrera.
     */
        $partidosQuery->where(function ($q) use ($campeonato_id, $carrera_id) {

            $q->whereHas('ci_local', function ($q2) use (
                $campeonato_id,
                $carrera_id
            ) {

                $q2->when(
                    $campeonato_id && $campeonato_id != 'todos',
                    function ($q3) use ($campeonato_id) {
                        $q3->where('campeonato_id', $campeonato_id);
                    }
                );

                $q2->when(
                    $carrera_id && $carrera_id != 'todos',
                    function ($q3) use ($carrera_id) {
                        $q3->where('carrera_id', $carrera_id);
                    }
                );
            });

            $q->orWhereHas('ci_visitante', function ($q2) use (
                $campeonato_id,
                $carrera_id
            ) {

                $q2->when(
                    $campeonato_id && $campeonato_id != 'todos',
                    function ($q3) use ($campeonato_id) {
                        $q3->where('campeonato_id', $campeonato_id);
                    }
                );

                $q2->when(
                    $carrera_id && $carrera_id != 'todos',
                    function ($q3) use ($carrera_id) {
                        $q3->where('carrera_id', $carrera_id);
                    }
                );
            });
        });

        // Filtro por fecha
        $partidosQuery->when(
            $fecha_ini && $fecha_fin,
            function ($q) use ($fecha_ini, $fecha_fin) {
                $q->whereBetween('fecha', [$fecha_ini, $fecha_fin]);
            }
        );

        $partidos = $partidosQuery->get();

        $canchaCancelada = 0;
        $canchaPendiente = 0;

        foreach ($partidos as $partido) {

            /*
        |--------------------------------------------------------------------------
        | LOCAL
        |--------------------------------------------------------------------------
        */

            if ($partido->ci_local_id) {

                $inscripcionLocal = CampeonatoInscripcion::find(
                    $partido->ci_local_id
                );

                if ($inscripcionLocal) {

                    $campeonatoValido =
                        $campeonato_id == 'todos' ||
                        $inscripcionLocal->campeonato_id == $campeonato_id;

                    $carreraValida =
                        !$carrera_id ||
                        $carrera_id == "todos" ||
                        $inscripcionLocal->carrera_id == $carrera_id;

                    if (
                        $campeonatoValido &&
                        $carreraValida &&
                        $partido->total_local > 0
                    ) {

                        if ($partido->pago_local == 1) {
                            $canchaCancelada += $partido->total_local;
                        } else {
                            $canchaPendiente += $partido->total_local;
                        }
                    }
                }
            }


            /*
        |--------------------------------------------------------------------------
        | VISITANTE
        |--------------------------------------------------------------------------
        */

            if ($partido->ci_visitante_id) {

                $inscripcionVisitante = CampeonatoInscripcion::find(
                    $partido->ci_visitante_id
                );

                if ($inscripcionVisitante) {

                    $campeonatoValido =
                        $campeonato_id == 'todos' ||
                        $inscripcionVisitante->campeonato_id == $campeonato_id;

                    $carreraValida =
                        !$carrera_id ||
                        $carrera_id == "todos" ||
                        $inscripcionVisitante->carrera_id == $carrera_id;

                    if (
                        $campeonatoValido &&
                        $carreraValida &&
                        $partido->total_visitante > 0
                    ) {

                        if ($partido->pago_visitante == 1) {
                            $canchaCancelada += $partido->total_visitante;
                        } else {
                            $canchaPendiente += $partido->total_visitante;
                        }
                    }
                }
            }
        }


        /*
    |--------------------------------------------------------------------------
    | AMARILLAS Y ROJAS
    |--------------------------------------------------------------------------
    */

        $detallesQuery = PartidoDetalle::whereHas(
            'campeonato_inscripcion',
            function ($q) use ($campeonato_id, $carrera_id) {

                /*
             * Si campeonato_id = todos, NO filtramos campeonato.
             */
                $q->when(
                    $campeonato_id && $campeonato_id != 'todos',
                    function ($q2) use ($campeonato_id) {
                        $q2->where('campeonato_id', $campeonato_id);
                    }
                );

                /*
             * Carrera
             */
                $q->when(
                    $carrera_id && $carrera_id != "todos",
                    function ($q2) use ($carrera_id) {
                        $q2->where('carrera_id', $carrera_id);
                    }
                );
            }
        );

        /*
     * Filtro de fechas mediante Partido
     */
        $detallesQuery->when(
            $fecha_ini && $fecha_fin,
            function ($q) use ($fecha_ini, $fecha_fin) {

                $q->whereHas('partido', function ($q2) use (
                    $fecha_ini,
                    $fecha_fin
                ) {
                    $q2->whereBetween(
                        'fecha',
                        [$fecha_ini, $fecha_fin]
                    );
                });
            }
        );

        $detalles = $detallesQuery->get();


        /*
    |--------------------------------------------------------------------------
    | AMARILLAS
    |--------------------------------------------------------------------------
    */

        $amarillasCanceladas = $detalles->sum(function ($detalle) {

            return $detalle->pagado_amarillas == 1
                ? $detalle->total_amarillas
                : 0;
        });

        $amarillasPendientes = $detalles->sum(function ($detalle) {

            return $detalle->pagado_amarillas == 0
                ? $detalle->total_amarillas
                : 0;
        });


        /*
    |--------------------------------------------------------------------------
    | ROJAS
    |--------------------------------------------------------------------------
    */

        $rojasCanceladas = $detalles->sum(function ($detalle) {

            return $detalle->pagado_rojas == 1
                ? $detalle->total_rojas
                : 0;
        });

        $rojasPendientes = $detalles->sum(function ($detalle) {

            return $detalle->pagado_rojas == 0
                ? $detalle->total_rojas
                : 0;
        });


        /*
    |--------------------------------------------------------------------------
    | RESULTADO FINAL
    |--------------------------------------------------------------------------
    */

        return response()->JSON([
            [
                'concepto' => 'INSCRIPCIONES',
                'cancelados' => round($inscripcionesCanceladas, 2),
                'pendientes' => round($inscripcionesPendientes, 2),
            ],

            [
                'concepto' => 'DERECHOS CANCHA',
                'cancelados' => round($canchaCancelada, 2),
                'pendientes' => round($canchaPendiente, 2),
            ],

            [
                'concepto' => 'AMARILLAS',
                'cancelados' => round($amarillasCanceladas, 2),
                'pendientes' => round($amarillasPendientes, 2),
            ],

            [
                'concepto' => 'ROJAS',
                'cancelados' => round($rojasCanceladas, 2),
                'pendientes' => round($rojasPendientes, 2),
            ],
        ]);
    }


    public function golesPorCarrera(Request $request)
    {
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $campeonato_id = $request->campeonato_id;
        $carrera_id = $request->carrera_id;

        /*
    |--------------------------------------------------------------------------
    | OBTENER LOS PARTIDOS
    |--------------------------------------------------------------------------
    */

        $partidosQuery = Partido::query()
            ->with([
                'ci_local.carrera',
                'ci_visitante.carrera',
            ]);

        /*
    |--------------------------------------------------------------------------
    | FILTRO DE CAMPEONATO
    |--------------------------------------------------------------------------
    */

        $partidosQuery->when(
            $campeonato_id && $campeonato_id != 'todos',
            function ($q) use ($campeonato_id) {

                $q->where(function ($q2) use ($campeonato_id) {

                    $q2->whereHas(
                        'ci_local',
                        function ($q3) use ($campeonato_id) {
                            $q3->where('campeonato_id', $campeonato_id);
                        }
                    )
                        ->orWhereHas(
                            'ci_visitante',
                            function ($q3) use ($campeonato_id) {
                                $q3->where('campeonato_id', $campeonato_id);
                            }
                        );
                });
            }
        );

        /*
    |--------------------------------------------------------------------------
    | FILTRO DE CARRERA
    |--------------------------------------------------------------------------
    */

        $partidosQuery->when(
            $carrera_id && $carrera_id != 'todos',
            function ($q) use ($carrera_id) {

                $q->where(function ($q2) use ($carrera_id) {

                    $q2->whereHas(
                        'ci_local',
                        function ($q3) use ($carrera_id) {
                            $q3->where('carrera_id', $carrera_id);
                        }
                    )
                        ->orWhereHas(
                            'ci_visitante',
                            function ($q3) use ($carrera_id) {
                                $q3->where('carrera_id', $carrera_id);
                            }
                        );
                });
            }
        );

        /*
    |--------------------------------------------------------------------------
    | FILTRO DE FECHAS
    |--------------------------------------------------------------------------
    */

        $partidosQuery->when(
            $fecha_ini && $fecha_fin,
            function ($q) use ($fecha_ini, $fecha_fin) {
                $q->whereBetween('fecha', [
                    $fecha_ini,
                    $fecha_fin
                ]);
            }
        );

        $partidos = $partidosQuery->get();


        /*
    |--------------------------------------------------------------------------
    | AGRUPAR GOLES POR CARRERA
    |--------------------------------------------------------------------------
    */

        $carreras = collect();

        foreach ($partidos as $partido) {

            /*
        |--------------------------------------------------------------------------
        | EQUIPO / CARRERA LOCAL
        |--------------------------------------------------------------------------
        */

            $inscripcionLocal = $partido->ci_local;

            if ($inscripcionLocal) {

                $carrera = $inscripcionLocal->carrera;

                if ($carrera) {

                    // Si se filtró una carrera, verificar
                    if (
                        $carrera_id &&
                        $carrera_id != 'todos' &&
                        $carrera->id != $carrera_id
                    ) {
                        // No agregar
                    } else {

                        if (!$carreras->has($carrera->id)) {
                            $carreras->put($carrera->id, [
                                'carrera_id' => $carrera->id,
                                'carrera' => $carrera->nombre,
                                'goles' => 0,
                            ]);
                        }

                        $item = $carreras->get($carrera->id);

                        $item['goles'] += (int) ($partido->goles_local ?? 0);

                        $carreras->put($carrera->id, $item);
                    }
                }
            }


            /*
        |--------------------------------------------------------------------------
        | EQUIPO / CARRERA VISITANTE
        |--------------------------------------------------------------------------
        */

            $inscripcionVisitante = $partido->ci_visitante;

            if ($inscripcionVisitante) {

                $carrera = $inscripcionVisitante->carrera;

                if ($carrera) {

                    if (
                        $carrera_id &&
                        $carrera_id != 'todos' &&
                        $carrera->id != $carrera_id
                    ) {
                        // No agregar
                    } else {

                        if (!$carreras->has($carrera->id)) {
                            $carreras->put($carrera->id, [
                                'carrera_id' => $carrera->id,
                                'carrera' => $carrera->nombre,
                                'goles' => 0,
                            ]);
                        }

                        $item = $carreras->get($carrera->id);

                        $item['goles'] += (int) ($partido->goles_visitante ?? 0);

                        $carreras->put($carrera->id, $item);
                    }
                }
            }
        }


        /*
    |--------------------------------------------------------------------------
    | RESULTADO
    |--------------------------------------------------------------------------
    */

        $carreras = $carreras
            ->sortByDesc('goles')
            ->values();

        return response()->json($carreras);
    }
}
