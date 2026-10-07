<?php

namespace App\Services;

use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class PermisoService
{
    protected $arrayPermisos = [
        "ADMINISTRADOR" => [
            "configuracions.index",
            "configuracions.create",
            "configuracions.edit",
            "configuracions.update",
            "configuracions.destroy",

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

            "tipo_usuarios.listado",

            "tipo_campeonatos.listado",

            "posicions.listado",

            "campeonatos.paginado",
            "campeonatos.index",
            "campeonatos.listado",
            "campeonatos.create",
            "campeonatos.store",
            "campeonatos.edit",
            "campeonatos.show",
            "campeonatos.update",
            "campeonatos.destroy",
            "campeonatos.finalizar",

            "campeonato_inscripcions.paginado",
            "campeonato_inscripcions.index",
            "campeonato_inscripcions.listado",
            "campeonato_inscripcions.create",
            "campeonato_inscripcions.store",
            "campeonato_inscripcions.edit",
            "campeonato_inscripcions.show",
            "campeonato_inscripcions.update",
            "campeonato_inscripcions.destroy",

            "campeonato_inscripcions.pagos",
            "campeonato_inscripcions.paginadoPagos",
            "campeonato_inscripcions.deudas",
            "campeonato_inscripcions.actualizaPago",
            "campeonato_inscripcions.posicions",
            "campeonato_inscripcions.goleadores",
            "campeonato_inscripcions.porteros",

            "costo_inscripcions.getCostoByTipo",

            "jugadors.paginado",
            "jugadors.index",
            "jugadors.listado",
            "jugadors.create",
            "jugadors.store",
            "jugadors.edit",
            "jugadors.show",
            "jugadors.update",
            "jugadors.destroy",

            "carrera_jugadors.paginado",
            "carrera_jugadors.index",
            "carrera_jugadors.listado",
            "carrera_jugadors.create",
            "carrera_jugadors.store",
            "carrera_jugadors.edit",
            "carrera_jugadors.show",
            "carrera_jugadors.update",
            "carrera_jugadors.destroy",

            "partidos.paginado",
            "partidos.index",
            "partidos.listado",
            "partidos.create",
            "partidos.store",
            "partidos.edit",
            "partidos.show",
            "partidos.update",
            "partidos.destroy",
            "partidos.iniciarPartido",
            "partidos.ver",
            "partidos.actualizarJugadores",
            "partidos.actualizaDatosPartido",
            "partidos.finalizarPartido",
            "partidos.detalles",

            "partido_detalles.actualizaDatosDetalle",

            "pagosCampeonato",
            "golesPorCarrera",

            "reportes.usuarios",
            "reportes.r_usuarios",
            "reportes.carreras",
            "reportes.r_carreras",
            "reportes.carrera_jugadors",
            "reportes.r_carrera_jugadors",
            "reportes.posicions",
            "reportes.r_posicions",
            "reportes.resultado_partidos",
            "reportes.r_resultado_partidos",
            "reportes.r_fixture",
            "reportes.r_partido_detalles",
            "r_partido_detalles",
            "reportes.goleadores",
            "reportes.r_goleadores",
            "reportes.porteros",
            "reportes.r_porteros",
            "reportes.pagos_pendientes",
            "reportes.r_pagos_pendientes",
        ],
        "AUXILIAR" => [
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

            "tipo_usuarios.listado",

            "tipo_campeonatos.listado",

            "posicions.listado",

            "campeonatos.paginado",
            "campeonatos.index",
            "campeonatos.listado",
            "campeonatos.create",
            "campeonatos.store",
            "campeonatos.edit",
            "campeonatos.show",
            "campeonatos.update",
            "campeonatos.destroy",
            "campeonatos.finalizar",

            "campeonato_inscripcions.paginado",
            "campeonato_inscripcions.index",
            "campeonato_inscripcions.listado",
            "campeonato_inscripcions.create",
            "campeonato_inscripcions.store",
            "campeonato_inscripcions.edit",
            "campeonato_inscripcions.show",
            "campeonato_inscripcions.update",
            "campeonato_inscripcions.destroy",

            "campeonato_inscripcions.pagos",
            "campeonato_inscripcions.paginadoPagos",
            "campeonato_inscripcions.deudas",
            // "campeonato_inscripcions.actualizaPago",
            "campeonato_inscripcions.posicions",
            "campeonato_inscripcions.goleadores",
            "campeonato_inscripcions.porteros",

            "costo_inscripcions.getCostoByTipo",

            "jugadors.paginado",
            "jugadors.index",
            "jugadors.listado",
            "jugadors.create",
            "jugadors.store",
            "jugadors.edit",
            "jugadors.show",
            "jugadors.update",
            "jugadors.destroy",

            "carrera_jugadors.paginado",
            "carrera_jugadors.index",
            "carrera_jugadors.listado",
            "carrera_jugadors.create",
            "carrera_jugadors.store",
            "carrera_jugadors.edit",
            "carrera_jugadors.show",
            "carrera_jugadors.update",
            "carrera_jugadors.destroy",

            "partidos.paginado",
            "partidos.index",
            "partidos.listado",
            "partidos.create",
            "partidos.store",
            "partidos.edit",
            "partidos.show",
            "partidos.update",
            "partidos.destroy",
            "partidos.iniciarPartido",
            "partidos.ver",
            "partidos.actualizarJugadores",
            "partidos.actualizaDatosPartido",
            "partidos.finalizarPartido",
            "partidos.detalles",

            "partido_detalles.actualizaDatosDetalle",

            "pagosCampeonato",
            "golesPorCarrera",

            "reportes.carreras",
            "reportes.r_carreras",
            "reportes.carrera_jugadors",
            "reportes.r_carrera_jugadors",
            "reportes.posicions",
            "reportes.r_posicions",
            "reportes.resultado_partidos",
            "reportes.r_resultado_partidos",
            "reportes.r_fixture",
            "reportes.r_partido_detalles",
            "r_partido_detalles",
            "reportes.goleadores",
            "reportes.r_goleadores",
            "reportes.porteros",
            "reportes.r_porteros",
            "reportes.pagos_pendientes",
            "reportes.r_pagos_pendientes",
        ],
        "MESA" => [
            "tipo_usuarios.listado",

            "tipo_campeonatos.listado",

            "posicions.listado",

            "campeonatos.paginado",
            "campeonatos.index",
            "campeonatos.listado",
            "campeonatos.show",

            "campeonato_inscripcions.paginado",
            "campeonato_inscripcions.index",
            "campeonato_inscripcions.listado",

            "costo_inscripcions.getCostoByTipo",

            "jugadors.paginado",
            "jugadors.index",
            "jugadors.listado",

            "carrera_jugadors.paginado",
            "carrera_jugadors.index",
            "carrera_jugadors.listado",

            "partidos.paginado",
            "partidos.index",
            "partidos.listado",
            "partidos.create",
            "partidos.store",
            "partidos.edit",
            "partidos.show",
            "partidos.update",
            "partidos.destroy",
            "partidos.iniciarPartido",
            "partidos.ver",
            "partidos.actualizarJugadores",
            "partidos.actualizaDatosPartido",
            "partidos.finalizarPartido",
            "partidos.detalles",

            "partido_detalles.actualizaDatosDetalle",

            "pagosCampeonato",
            "golesPorCarrera",

            "reportes.posicions",
            "reportes.r_posicions",
            "reportes.resultado_partidos",
            "reportes.r_resultado_partidos",
            "reportes.r_fixture",
            "reportes.r_partido_detalles",
            "r_partido_detalles",
            "reportes.goleadores",
            "reportes.r_goleadores",
            "reportes.porteros",
            "reportes.r_porteros",
        ],
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
