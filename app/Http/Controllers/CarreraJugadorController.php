<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarreraJugadorStoreRequest;
use App\Http\Requests\CarreraJugadorUpdateRequest;
use App\Models\CarreraJugador;
use App\Models\User;
use App\Services\CarreraJugadorService;
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

class CarreraJugadorController extends Controller
{
    public function __construct(private CarreraJugadorService $carrera_jugadorService) {}

    /**
     * Página index
     *
     * @return Response
     */
    public function index(): ResponseInertia
    {
        return Inertia::render("Admin/CarreraJugadors/Index");
    }

    /**
     * Listado de carrera_jugadors sin ids: 1 y 2
     *
     * @return JsonResponse
     */
    public function listado(Request $request): JsonResponse
    {
        return response()->JSON([
            "carrera_jugadors" => $this->carrera_jugadorService->listado(
                $request->input("carrera_id", ""),
                $request->input("campeonato_inscripcion_id", "")
            )
        ]);
    }

    public function paginado(Request $request)
    {
        $perPage = $request->perPage;
        $page = (int)($request->input("page", 1));
        $search = (string)$request->input("search", "");
        $campeonato_id = (string)$request->input("campeonato_id", 0);
        $porCampeonato = (string)$request->input("porCampeonato", true);
        $orderBy = $request->orderBy;
        $orderAsc = $request->orderAsc;

        $arrayOrderBy = [];
        if ($orderBy && $orderAsc) {
            $arrayOrderBy = [
                [$orderBy, $orderAsc]
            ];
        }

        $carrera_jugadors = $this->carrera_jugadorService->listadoPaginado(
            $perPage,
            $page,
            $search,
            $campeonato_id,
            $porCampeonato,
            $arrayOrderBy
        );
        return response()->JSON([
            "data" => $carrera_jugadors->items(),
            "total" => $carrera_jugadors->total(),
            "lastPage" => $carrera_jugadors->lastPage()
        ]);
    }

    /**
     * Registrar un nuevo carrera_jugador
     *
     * @param CarreraJugadorStoreRequest $request
     * @return RedirectResponse|Response
     */
    public function store(CarreraJugadorStoreRequest $request): RedirectResponse|Response|JsonResponse
    {
        DB::beginTransaction();
        try {
            // crear el CarreraJugador
            $this->carrera_jugadorService->crear($request->validated());
            DB::commit();
            return response()->JSON([
                "sw" => true,
                "message" => "Registro realizado",
            ]);
            // return redirect()->route("carrera_jugadors.index")->with("bien", "Registro realizado");
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostrar un carrera_jugador
     *
     * @param CarreraJugador $carrera_jugador
     * @return JsonResponse
     */
    public function show(CarreraJugador $carrera_jugador): JsonResponse
    {
        return response()->JSON($carrera_jugador);
    }

    public function update(CarreraJugador $carrera_jugador, CarreraJugadorUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            // actualizar carrera_jugador
            $this->carrera_jugadorService->actualizar($request->validated(), $carrera_jugador);
            DB::commit();
            return response()->JSON([
                "sw" => true,
                "message" => "Registro actualizado",
            ]);
            // return redirect()->route("carrera_jugadors.index")->with("bien", "Registro actualizado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar carrera_jugador
     *
     * @param CarreraJugador $carrera_jugador
     * @return JsonResponse|Response
     */
    public function destroy(CarreraJugador $carrera_jugador): JsonResponse|Response
    {
        DB::beginTransaction();
        try {
            $this->carrera_jugadorService->eliminar($carrera_jugador);
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
