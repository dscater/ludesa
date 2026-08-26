<?php

namespace App\Services;

use App\Models\Certificado;
use App\Services\HistorialAccionService;
use App\Models\Cliente;
use App\Models\User;
use DateTime;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ClienteService
{
    private $modulo = "CLIENTES";

    public function __construct(private  CertificadoService $certificado_service, private HistorialAccionService $historialAccionService) {}

    public function listado(): Collection
    {
        $clientes = Cliente::select("clientes.*")->where("status", 1)->get();
        return $clientes;
    }
    /**
     * Lista de clientes paginado con filtros
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
        $clientes = Cliente::select("clientes.*")
            ->with("user:id,nombre,paterno,materno")
            ->where("status", 1);

        $clientes->where(function ($query) use ($search) {

            $query->buscarNombre($search);

            $query->orWhereHas("user", function ($sub) use ($search) {
                $sub->whereRaw(
                    "CONCAT(nombre, ' ', paterno, ' ', materno) LIKE ?",
                    ["%{$search}%"]
                );
            });
        });

        // Ordenamiento
        foreach ($orderBy as $value) {
            if (isset($value[0], $value[1])) {
                $clientes->orderBy($value[0], $value[1]);
            }
        }

        $clientes = $clientes->paginate($length, ['*'], 'page', $page);
        return $clientes;
    }

    public function listadoPaginadoEliminados(int $length, int $page, string $search, array $columnsSerachLike = [], array $columnsFilter = [], array $columnsBetweenFilter = [], array $orderBy = []): LengthAwarePaginator
    {
        $clientes = Cliente::select("clientes.*")
            ->with("user:id,nombre,paterno,materno")
            ->where("status", 0);

        $clientes->where(function ($query) use ($search) {

            $query->buscarNombre($search);

            $query->orWhereHas("user", function ($sub) use ($search) {
                $sub->whereRaw(
                    "CONCAT(nombre, ' ', paterno, ' ', materno) LIKE ?",
                    ["%{$search}%"]
                );
            });
        });

        // Ordenamiento
        foreach ($orderBy as $value) {
            if (isset($value[0], $value[1])) {
                $clientes->orderBy($value[0], $value[1]);
            }
        }

        $clientes = $clientes->paginate($length, ['*'], 'page', $page);
        return $clientes;
    }

    /**
     * Crear cliente
     *
     * @param array $datos
     * @return Cliente
     */
    public function crear(array $datos): Cliente
    {
        $cliente = Cliente::create([
            "nombre" => mb_strtoupper($datos['nombre']),
            "paterno" => mb_strtoupper($datos['paterno']),
            "materno" => mb_strtoupper($datos['materno']),
            "ci" => mb_strtoupper($datos['ci']),
            "ci_exp" => mb_strtoupper($datos['ci_exp']),
            "complemento" => mb_strtoupper($datos['complemento']),
            "fecha_nac" => $datos['fecha_nac'],
            "edad" => isset($datos['edad']) && $datos['edad'] ? $datos["edad"] : $this->calcularEdad($datos["fecha_nac"]),
            "cel" => $datos['cel'],
            "fecha_registro" => date("Y-m-d"),
            "user_id" => Auth::user()->id,
        ]);

        if ($datos["con_certificado"]) {
            $datos_certificado = [
                "cliente_id" => $cliente->id,
                "total" => $datos["total"],
                "cancelado" => $datos["cancelado"],
                "saldo" => $datos["saldo"],
                "tipo_pago" => $datos["tipo_pago"],
                "estado" => 0,
                "tipo" => $datos["tipo"],
                "tramitador_id" => $datos["tramitador_id"],
                "certificado_detalles" => $datos["certificado_detalles"],
            ];
            $this->certificado_service->crear($datos_certificado);
        }

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "CREACIÓN", "REGISTRO UN CLIENTE", $cliente);

        return $cliente;
    }

    /**
     * Actualizar cliente
     *
     * @param array $datos
     * @param Cliente $cliente
     * @return Cliente
     */
    public function actualizar(array $datos, Cliente $cliente): Cliente
    {
        $old_cliente = clone $cliente;

        $cliente->update([
            "nombre" => mb_strtoupper($datos['nombre']),
            "paterno" => mb_strtoupper($datos['paterno']),
            "materno" => mb_strtoupper($datos['materno']),
            "ci" => mb_strtoupper($datos['ci']),
            "ci_exp" => mb_strtoupper($datos['ci_exp']),
            "complemento" => mb_strtoupper($datos['complemento']),
            "edad" => isset($datos['edad']) && $datos['edad'] ? $datos["edad"] : $this->calcularEdad($datos["fecha_nac"]),
            "cel" => $datos['cel'],
        ]);

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "MODIFICACIÓN", "ACTUALIZÓ UN CLIENTE", $old_cliente, $cliente->withoutRelations());

        return $cliente;
    }

    /**
     * Eliminar cliente
     *
     * @param Cliente $cliente
     * @return boolean
     */
    public function eliminar(Cliente $cliente): bool|Exception
    {
        $old_cliente = clone $cliente;

        $usos = Certificado::where("cliente_id", $cliente->id)->count();
        if ($usos > 0) {
            throw ValidationException::withMessages(["cliente" => "No se puede eliminar el cliente porque tiene certificados asociados."]);
        }

        $cliente->status = 0;
        $cliente->save();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ UN CLIENTE", $old_cliente, $cliente);

        return true;
    }

    public function restaurar(Cliente $cliente): bool|Exception
    {
        $old_cliente = clone $cliente;
        $cliente->status = 1;
        $cliente->save();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "RESTAURACIÓN", "RESTAURÓ UN CLIENTE", $old_cliente, $cliente);

        return true;
    }

    public function eliminacionPermanente(Cliente $cliente): bool|Exception
    {
        $old_cliente = clone $cliente;
        $usos = Certificado::where("cliente_id", $cliente->id)->count();

        if ($usos > 0) {
            throw ValidationException::withMessages(["cliente" => "No se puede eliminar el cliente porque tiene certificados asociados."]);
        }

        $cliente->delete();

        // registrar accion
        $this->historialAccionService->registrarAccion($this->modulo, "ELIMINACIÓN", "ELIMINÓ PERMANENTEMENTE UN CLIENTE", $old_cliente, $cliente);

        return true;
    }

    public function calcularEdad($fechaNacimiento)
    {
        if (!$fechaNacimiento) return null;

        $fechaNac = new DateTime($fechaNacimiento);
        $hoy = new DateTime();

        $edad = $hoy->diff($fechaNac);

        return $edad->y; // años
    }
}
