<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartidoStoreRequest;
use App\Http\Requests\PartidoUpdateRequest;
use App\Models\Partido;
use App\Models\PartidoDetalle;
use App\Models\User;
use App\Services\CampeonatoInscripcionService;
use App\Services\CostoDerechoService;
use App\Services\CostoTarjetaService;
use App\Services\PartidoService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as ResponseInertia;

class PartidoController extends Controller
{
    public function __construct(
        private PartidoService $partidoService,
        private CostoTarjetaService $costo_tarjeta_service,
        private CostoDerechoService $costo_derecho_service,
        private CampeonatoInscripcionService $campeonato_inscripcion_service
    ) {}

    /**
     * Página index
     *
     * @return Response
     */
    public function index(): ResponseInertia
    {
        return Inertia::render("Admin/Partidos/Index");
    }

    /**
     * Listado de partidos sin ids: 1 y 2
     *
     * @return JsonResponse
     */
    public function listado(Request $request): JsonResponse
    {
        return response()->JSON([
            "partidos" => $this->partidoService->listado($request->input("campeonato_id", ""))
        ]);
    }

    public function paginado(Request $request)
    {
        $perPage = $request->perPage;
        $page = (int)($request->input("page", 1));
        $search = (string)$request->input("search", "");
        $campeonato_id = (string)$request->input("campeonato_id", 0);
        $fecha_ini = (string)$request->input("fecha_ini", "");
        $fecha_fin = (string)$request->input("fecha_fin", "");
        $estado = (string)$request->input("estado", "");
        $porCampeonato = (string)$request->input("porCampeonato", true);
        $orderBy = $request->orderBy;
        $orderAsc = $request->orderAsc;

        $arrayOrderBy = [
            ["fecha", "desc"],
            ["hora", "desc"],
        ];
        // if ($orderBy && $orderAsc) {
        //     $arrayOrderBy = [
        //         [$orderBy, $orderAsc]
        //     ];
        // }

        $partidos = $this->partidoService->listadoPaginado(
            $perPage,
            $page,
            $search,
            $campeonato_id,
            $fecha_ini,
            $fecha_fin,
            $estado,
            $porCampeonato,
            $arrayOrderBy
        );
        return response()->JSON([
            "data" => $partidos->items(),
            "total" => $partidos->total(),
            "lastPage" => $partidos->lastPage()
        ]);
    }

    /**
     * Registrar un nuevo partido
     *
     * @param PartidoStoreRequest $request
     * @return RedirectResponse|Response
     */
    public function store(PartidoStoreRequest $request): RedirectResponse|Response
    {
        DB::beginTransaction();
        try {
            // crear el Partido
            $this->partidoService->crear($request->validated());
            DB::commit();
            return redirect()->route("partidos.index")->with("bien", "Registro realizado");
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostrar un partido
     *
     * @param Partido $partido
     * @return JsonResponse
     */
    public function show(Partido $partido): JsonResponse
    {
        return response()->JSON($partido);
    }

    public function ver(Partido $partido)
    {
        $campeonato = $partido->campeonato;
        $partido = $partido->load(["ci_local.carrera", "ci_local.campeonato", "ci_visitante.carrera", "ci_visitante.campeonato", "partido_detalles"]);

        $local_detalles = PartidoDetalle::with(["carrera_jugador.jugador"])
            ->where("partido_id", $partido->id)
            ->where("campeonato_inscripcion_id", $partido->ci_local_id)
            ->get();

        $visitante_detalles = PartidoDetalle::with(["carrera_jugador.jugador"])
            ->where("partido_id", $partido->id)
            ->where("campeonato_inscripcion_id", $partido->ci_visitante_id)
            ->get();

        $costo_tarjetas = $this->costo_tarjeta_service->getCostosTipo($campeonato->tipo);
        $costo_derechos = $this->costo_derecho_service->getCostosTipo($campeonato->tipo);

        $deudas_local = $this->campeonato_inscripcion_service->deudas($partido->ci_local, $partido->id);
        $deudas_visitante = $this->campeonato_inscripcion_service->deudas($partido->ci_visitante, $partido->id);

        return Inertia::render("Admin/Partidos/Show", compact(
            "campeonato",
            "partido",
            "local_detalles",
            "visitante_detalles",
            "costo_tarjetas",
            "costo_derechos",
            "deudas_local",
            "deudas_visitante"
        ));
    }

    public function iniciarPartido(Partido $partido, Request $request)
    {
        DB::beginTransaction();
        try {
            // actualizar partido
            $partido = $this->partidoService->iniciarPartido($partido);
            DB::commit();
            if ($request->ajax()) {
                return response()->JSON([
                    "sw" => true,
                    "message" => "Partido iniciado"
                ]);
            }
            return redirect()->route("partidos.ver", $partido->id)->with("bien", "Registro iniciado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    public function finalizarPartido(Partido $partido, Request $request)
    {
        DB::beginTransaction();
        try {
            // actualizar partido
            $partido = $this->partidoService->finalizarPartido($partido);
            DB::commit();
            if ($request->ajax()) {
                return response()->JSON([
                    "sw" => true,
                    "message" => "Partido finalizado"
                ]);
            }
            return redirect()->route("partidos.index", $partido->id)->with("bien", "Registro iniciado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    public function actualizarJugadores(Partido $partido)
    {
        DB::beginTransaction();
        try {
            // actualizar partido
            $partido = $this->partidoService->actualizarJugadoresPartido($partido);
            $local_detalles = PartidoDetalle::with(["carrera_jugador.jugador"])
                ->where("partido_id", $partido->id)
                ->where("campeonato_inscripcion_id", $partido->ci_local_id)
                ->get();

            $visitante_detalles = PartidoDetalle::with(["carrera_jugador.jugador"])
                ->where("partido_id", $partido->id)
                ->where("campeonato_inscripcion_id", $partido->ci_visitante_id)
                ->get();
            DB::commit();
            return response()->JSON([
                "sw" => true,
                "message" => "Registros actualizados",
                "local_detalles" => $local_detalles,
                "visitante_detalles" => $visitante_detalles,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    public function actualizaDatosPartido(Partido $partido, Request $request)
    {
        DB::beginTransaction();
        try {
            // actualizar partido
            $partido = $this->partidoService->actualizaDatosPartido($partido, $request->col, $request->data);
            DB::commit();
            return response()->JSON([
                "sw" => true,
                "message" => "Registros actualizados",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    public function update(Partido $partido, PartidoUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            // actualizar partido
            $this->partidoService->actualizar($request->validated(), $partido);
            DB::commit();
            return redirect()->route("partidos.index")->with("bien", "Registro actualizado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar partido
     *
     * @param Partido $partido
     * @return JsonResponse|Response
     */
    public function destroy(Partido $partido): JsonResponse|Response
    {
        DB::beginTransaction();
        try {
            $this->partidoService->eliminar($partido);
            DB::commit();
            return response()->JSON([
                'sw' => true,
                'message' => 'El registro se eliminó correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }
}
