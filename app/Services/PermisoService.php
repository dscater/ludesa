<?php

namespace App\Services;

use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class PermisoService
{
    protected $arrayPermisos = [
        "ADMINISTRADOR" => [
            "usuarios.paginado",
            "usuarios.index",
            "usuarios.listado",
            "usuarios.create",
            "usuarios.store",
            "usuarios.edit",
            "usuarios.show",
            "usuarios.update",
            "usuarios.destroy",
            "usuarios.password",
            "usuarios.byTipo",

            "tipo_usuarios.listado",

            "carreras.paginado",
            "carreras.index",
            "carreras.listado",
            "carreras.create",
            "carreras.store",
            "carreras.edit",
            "carreras.show",
            "carreras.update",
            "carreras.destroy",

            "tipo_campeonatos.listado",

            "campeonatos.paginado",
            "campeonatos.index",
            "campeonatos.listado",
            "campeonatos.create",
            "campeonatos.store",
            "campeonatos.edit",
            "campeonatos.show",
            "campeonatos.update",
            "campeonatos.destroy",

            "campeonato_inscripcions.paginado",
            "campeonato_inscripcions.index",
            "campeonato_inscripcions.listado",
            "campeonato_inscripcions.create",
            "campeonato_inscripcions.store",
            "campeonato_inscripcions.edit",
            "campeonato_inscripcions.show",
            "campeonato_inscripcions.update",
            "campeonato_inscripcions.destroy",

            "jugadors.paginado",
            "jugadors.index",
            "jugadors.listado",
            "jugadors.create",
            "jugadors.store",
            "jugadors.edit",
            "jugadors.show",
            "jugadors.update",
            "jugadors.destroy",

            "reportes.usuarios",
            "reportes.r_usuarios",

        ],
        "AUXILIAR" => [],
        "MESA" => [],
    ];



    public function getTiposUsuarios()
    {
        return array_keys($this->arrayPermisos);
    }

    /**
     * Obtener permisos de usuario logeado
     *
     * @return array
     */
    public function getPermisosUser(): array|string
    {
        $user = Auth::user();
        $permisos = [];
        if ($user) {
            return $this->arrayPermisos[$user->tipo];
        }

        return $permisos;
    }
}
