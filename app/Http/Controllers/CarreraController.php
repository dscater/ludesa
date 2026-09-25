<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarreraStoreRequest;
use App\Http\Requests\CarreraUpdateRequest;
use App\Models\Carrera;
use App\Models\User;
use App\Services\CarreraService;
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

class CarreraController extends Controller
{
    public function __construct(private CarreraService $carreraService) {}

    /**
     * Página index
     *
     * @return Response
     */
    public function index(): ResponseInertia
    {
        return Inertia::render("Admin/Carreras/Index");
    }

    /**
     * Listado de carreras sin ids: 1 y 2
     *
     * @return JsonResponse
     */
    public function listado(Request $request): JsonResponse
    {
        return response()->JSON([
            "carreras" => $this->carreraService->listado(
                $request->input("campeonato_id", null),
                $request->input("sin_inscripcion", false)
            )
        ]);
    }

    public function paginado(Request $request)
    {
        $perPage = $request->perPage;
        $page = (int)($request->input("page", 1));
        $search = (string)$request->input("search", "");
        $orderBy = $request->orderBy;
        $orderAsc = $request->orderAsc;

        $columnsSerachLike = [
            "nombre",
            "descripcion",
        ];
        $columnsFilter = [];
        $columnsBetweenFilter = [];
        $arrayOrderBy = [];
        if ($orderBy && $orderAsc) {
            $arrayOrderBy = [
                [$orderBy, $orderAsc]
            ];
        }

        $carreras = $this->carreraService->listadoPaginado($perPage, $page, $search, $columnsSerachLike, $columnsFilter, $columnsBetweenFilter, $arrayOrderBy);
        return response()->JSON([
            "data" => $carreras->items(),
            "total" => $carreras->total(),
            "lastPage" => $carreras->lastPage()
        ]);
    }

    /**
     * Registrar un nuevo carrera
     *
     * @param CarreraStoreRequest $request
     * @return RedirectResponse|Response
     */
    public function store(CarreraStoreRequest $request): RedirectResponse|Response
    {
        DB::beginTransaction();
        try {
            // crear el Carrera
            $this->carreraService->crear($request->validated());
            DB::commit();
            return redirect()->route("carreras.index")->with("bien", "Registro realizado");
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostrar un carrera
     *
     * @param Carrera $carrera
     * @return JsonResponse
     */
    public function show(Carrera $carrera): JsonResponse
    {
        return response()->JSON($carrera);
    }

    public function update(Carrera $carrera, CarreraUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            // actualizar carrera
            $this->carreraService->actualizar($request->validated(), $carrera);
            DB::commit();
            return redirect()->route("carreras.index")->with("bien", "Registro actualizado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar carrera
     *
     * @param Carrera $carrera
     * @return JsonResponse|Response
     */
    public function destroy(Carrera $carrera): JsonResponse|Response
    {
        DB::beginTransaction();
        try {
            $this->carreraService->eliminar($carrera);
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
