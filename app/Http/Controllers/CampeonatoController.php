<?php

namespace App\Http\Controllers;

use App\Http\Requests\CampeonatoStoreRequest;
use App\Http\Requests\CampeonatoUpdateRequest;
use App\Models\Campeonato;
use App\Models\User;
use App\Services\CampeonatoService;
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

class CampeonatoController extends Controller
{
    public function __construct(private CampeonatoService $campeonatoService) {}

    /**
     * Página index
     *
     * @return Response
     */
    public function index(): ResponseInertia
    {
        return Inertia::render("Admin/Campeonatos/Index");
    }

    /**
     * Listado de campeonatos sin ids: 1 y 2
     *
     * @return JsonResponse
     */
    public function listado(): JsonResponse
    {
        return response()->JSON([
            "campeonatos" => $this->campeonatoService->listado()
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

        $campeonatos = $this->campeonatoService->listadoPaginado($perPage, $page, $search, $columnsSerachLike, $columnsFilter, $columnsBetweenFilter, $arrayOrderBy);
        return response()->JSON([
            "data" => $campeonatos->items(),
            "total" => $campeonatos->total(),
            "lastPage" => $campeonatos->lastPage()
        ]);
    }

    /**
     * Registrar un nuevo campeonato
     *
     * @param CampeonatoStoreRequest $request
     * @return RedirectResponse|Response
     */
    public function store(CampeonatoStoreRequest $request): RedirectResponse|Response
    {
        DB::beginTransaction();
        try {
            // crear el Campeonato
            $this->campeonatoService->crear($request->validated());
            DB::commit();
            return redirect()->route("campeonatos.index")->with("bien", "Registro realizado");
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostrar un campeonato
     *
     * @param Campeonato $campeonato
     * @return JsonResponse
     */
    public function show(Campeonato $campeonato): JsonResponse
    {
        return response()->JSON($campeonato);
    }

    public function update(Campeonato $campeonato, CampeonatoUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            // actualizar campeonato
            $this->campeonatoService->actualizar($request->validated(), $campeonato);
            DB::commit();
            return redirect()->route("campeonatos.index")->with("bien", "Registro actualizado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar campeonato
     *
     * @param Campeonato $campeonato
     * @return JsonResponse|Response
     */
    public function destroy(Campeonato $campeonato): JsonResponse|Response
    {
        DB::beginTransaction();
        try {
            $this->campeonatoService->eliminar($campeonato);
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
