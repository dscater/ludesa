<?php

namespace App\Http\Controllers;

use App\Http\Requests\JugadorStoreRequest;
use App\Http\Requests\JugadorUpdateRequest;
use App\Models\Jugador;
use App\Models\User;
use App\Services\JugadorService;
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

class JugadorController extends Controller
{
    public function __construct(private JugadorService $jugadorService) {}

    /**
     * Página index
     *
     * @return Response
     */
    public function index(): ResponseInertia
    {
        return Inertia::render("Admin/Jugadors/Index");
    }

    /**
     * Listado de jugadors sin ids: 1 y 2
     *
     * @return JsonResponse
     */
    public function listado(Request $request): JsonResponse
    {
        return response()->JSON([
            "jugadors" => $this->jugadorService->listado($request->input("activo", null))
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

        $jugadors = $this->jugadorService->listadoPaginado($perPage, $page, $search, $columnsSerachLike, $columnsFilter, $columnsBetweenFilter, $arrayOrderBy);
        return response()->JSON([
            "data" => $jugadors->items(),
            "total" => $jugadors->total(),
            "lastPage" => $jugadors->lastPage()
        ]);
    }

    /**
     * Registrar un nuevo jugador
     *
     * @param JugadorStoreRequest $request
     * @return RedirectResponse|Response
     */
    public function store(JugadorStoreRequest $request): RedirectResponse|Response
    {
        DB::beginTransaction();
        try {
            // crear el Jugador
            $this->jugadorService->crear($request->validated());
            DB::commit();
            return redirect()->route("jugadors.index")->with("bien", "Registro realizado");
        } catch (\Exception $e) {
            DB::rollBack();
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostrar un jugador
     *
     * @param Jugador $jugador
     * @return JsonResponse
     */
    public function show(Jugador $jugador): JsonResponse
    {
        return response()->JSON($jugador);
    }

    public function update(Jugador $jugador, JugadorUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            // actualizar jugador
            $this->jugadorService->actualizar($request->validated(), $jugador);
            DB::commit();
            return redirect()->route("jugadors.index")->with("bien", "Registro actualizado");
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::debug($e->getMessage());
            throw ValidationException::withMessages([
                'error' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar jugador
     *
     * @param Jugador $jugador
     * @return JsonResponse|Response
     */
    public function destroy(Jugador $jugador): JsonResponse|Response
    {
        DB::beginTransaction();
        try {
            $this->jugadorService->eliminar($jugador);
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
