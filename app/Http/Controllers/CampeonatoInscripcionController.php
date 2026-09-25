<?php

namespace App\Http\Controllers;

use App\Http\Requests\CampeonatoInscripcionStoreRequest;
use App\Http\Requests\CampeonatoInscripcionUpdateRequest;
use App\Models\CampeonatoInscripcion;
use App\Models\User;
use App\Services\CampeonatoInscripcionService;
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

class CampeonatoInscripcionController extends Controller
{
    public function __construct(private CampeonatoInscripcionService $campeonato_inscripcionService) {}

    /**
     * Página index
     *
     * @return Response
     */
    public function index(): ResponseInertia
    {
        return Inertia::render("Admin/CampeonatoInscripcions/Index");
    }

    /**
     * Listado de campeonato_inscripcions sin ids: 1 y 2
     *
     * @return JsonResponse
     */
    public function listado(): JsonResponse
    {
        return response()->JSON([
            "campeonato_inscripcions" => $this->campeonato_inscripcionService->listado()
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

        $campeonato_inscripcions = $this->campeonato_inscripcionService->listadoPaginado(
            $perPage,
            $page,
            $search,
            $campeonato_id,
            $porCampeonato,
            $arrayOrderBy
        );
        return response()->JSON([
            "data" => $campeonato_inscripcions->items(),
            "total" => $campeonato_inscripcions->total(),
            "lastPage" => $campeonato_inscripcions->lastPage()
        ]);
    }

    /**
     * Registrar un nuevo campeonato_inscripcion
     *
     * @param CampeonatoInscripcionStoreRequest $request
     * @return RedirectResponse|Response
     */
    public function store(CampeonatoInscripcionStoreRequest $request): RedirectResponse|Response
    {
        DB::beginTransaction();
        try {
            // crear el CampeonatoInscripcion
            $this->campeonato_inscripcionService->crear($request->validated());
            DB::commit();
            return redirect()->route("campeonato_inscripcions.index")->with("bien", "Registro realizado");
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostrar un campeonato_inscripcion
     *
     * @param CampeonatoInscripcion $campeonato_inscripcion
     * @return JsonResponse
     */
    public function show(CampeonatoInscripcion $campeonato_inscripcion): JsonResponse
    {
        return response()->JSON($campeonato_inscripcion);
    }

    public function update(CampeonatoInscripcion $campeonato_inscripcion, CampeonatoInscripcionUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            // actualizar campeonato_inscripcion
            $this->campeonato_inscripcionService->actualizar($request->validated(), $campeonato_inscripcion);
            DB::commit();
            return redirect()->route("campeonato_inscripcions.index")->with("bien", "Registro actualizado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar campeonato_inscripcion
     *
     * @param CampeonatoInscripcion $campeonato_inscripcion
     * @return JsonResponse|Response
     */
    public function destroy(CampeonatoInscripcion $campeonato_inscripcion): JsonResponse|Response
    {
        DB::beginTransaction();
        try {
            $this->campeonato_inscripcionService->eliminar($campeonato_inscripcion);
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
