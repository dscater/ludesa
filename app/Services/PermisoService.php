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

            "sucursals.paginado",
            "sucursals.index",
            "sucursals.listado",
            "sucursals.create",
            "sucursals.store",
            "sucursals.edit",
            "sucursals.show",
            "sucursals.update",
            "sucursals.destroy",

            "clientes.paginado",
            "clientes.index",
            "clientes.listado",
            "clientes.byCi",
            "clientes.create",
            "clientes.store",
            "clientes.nuevo",
            "clientes.edit",
            "clientes.show",
            "clientes.update",
            "clientes.destroy",
            "clientes.eliminados",
            "clientes.restaurar",
            "clientes.eliminacionPermanente",
            "clientes.paginadoEliminados",

            "tramitadors.paginado",
            "tramitadors.index",
            "tramitadors.listado",
            "tramitadors.create",
            "tramitadors.store",
            "tramitadors.edit",
            "tramitadors.show",
            "tramitadors.update",
            "tramitadors.destroy",

            "tipo_certificados.paginado",
            "tipo_certificados.index",
            "tipo_certificados.listado",
            "tipo_certificados.create",
            "tipo_certificados.store",
            "tipo_certificados.edit",
            "tipo_certificados.show",
            "tipo_certificados.update",
            "tipo_certificados.destroy",

            "tipo_pagos.listado",

            "certificados.paginado",
            "certificados.listadoCobros",
            "certificados.index",
            "certificados.listado",
            "certificados.create",
            "certificados.store",
            "certificados.edit",
            "certificados.show",
            "certificados.update",
            "certificados.destroy",
            "certificados.verificaPendienteCliente",
            "certificados.registroCliente",
            "certificados.editDetalles",
            "certificados.updateDetalles",
            "certificados.eliminados",
            "certificados.restaurar",
            "certificados.eliminacionPermanente",
            "certificados.paginadoEliminados",

            "certificado_emitidos.verificaCantidad",

            "cobros.index",
            "cobros.registrarPago",

            // "recepcion_pagos.index",

            "pagos.index",
            "pagos.verificados",
            "pagos.registrarPagos",

            "configuracions.index",
            "configuracions.create",
            "configuracions.edit",
            "configuracions.update",
            "configuracions.destroy",

            "certificadosEmitidosLinea",
            "cantidadTramitesNormal",

            "login_users.verificaSucursal",
            "login_users.asignaSucursal",

            "reportes.usuarios",
            "reportes.r_usuarios",
            "reportes.exportarCaja",
            "reportes.clientes",
            "reportes.r_clientes",
            "reportes.certificados",
            "reportes.r_certificados",
            "reportes.certificados_interno",
            "reportes.r_certificados_interno",
            "reportes.r_certificados_diario",
            "reportes.gcemitidos",
            "reportes.r_gcemitidos",
            "reportes.gmemitidos",
            "reportes.r_gmemitidos",
            "reportes.historial_accions",
            "reportes.r_historial_accions",

        ],
        "GERENTE" => [
            "usuarios.listado",
            "usuarios.byTipo",

            "tipo_usuarios.listado",

            "sucursals.paginado",
            "sucursals.index",
            "sucursals.listado",
            "sucursals.create",
            "sucursals.store",
            "sucursals.edit",
            "sucursals.show",
            "sucursals.update",
            "sucursals.destroy",

            "clientes.paginado",
            "clientes.index",
            "clientes.listado",
            "clientes.byCi",
            "clientes.create",
            "clientes.store",
            "clientes.nuevo",
            "clientes.edit",
            "clientes.show",
            "clientes.update",
            "clientes.destroy",
            "clientes.eliminados",
            "clientes.restaurar",
            "clientes.eliminacionPermanente",
            "clientes.paginadoEliminados",

            "tramitadors.paginado",
            "tramitadors.index",
            "tramitadors.listado",
            "tramitadors.create",
            "tramitadors.store",
            "tramitadors.edit",
            "tramitadors.show",
            "tramitadors.update",
            "tramitadors.destroy",

            "tipo_certificados.paginado",
            "tipo_certificados.index",
            "tipo_certificados.listado",
            "tipo_certificados.create",
            "tipo_certificados.store",
            "tipo_certificados.edit",
            "tipo_certificados.show",
            "tipo_certificados.update",
            "tipo_certificados.destroy",

            "tipo_pagos.listado",

            "certificados.paginado",
            "certificados.listadoCobros",
            "certificados.index",
            "certificados.listado",
            "certificados.create",
            "certificados.store",
            "certificados.edit",
            "certificados.show",
            "certificados.update",
            "certificados.destroy",
            "certificados.verificaPendienteCliente",
            "certificados.registroCliente",
            "certificados.editDetalles",
            "certificados.updateDetalles",
            "certificados.eliminados",
            "certificados.restaurar",
            "certificados.eliminacionPermanente",
            "certificados.paginadoEliminados",

            "certificado_emitidos.verificaCantidad",

            "cobros.index",
            "cobros.registrarPago",

            // "recepcion_pagos.index",

            "pagos.index",
            "pagos.verificados",
            // "pagos.registrarPagos",

            // "configuracions.index",
            // "configuracions.create",
            // "configuracions.edit",
            // "configuracions.update",
            // "configuracions.destroy",

            "certificadosEmitidosLinea",
            "cantidadTramitesNormal",

            "login_users.verificaSucursal",
            "login_users.asignaSucursal",

            "reportes.exportarCaja",
            "reportes.clientes",
            "reportes.r_clientes",
            "reportes.certificados",
            "reportes.r_certificados",
            "reportes.certificados_interno",
            "reportes.r_certificados_interno",
            "reportes.r_certificados_diario",
            "reportes.gcemitidos",
            "reportes.r_gcemitidos",
            "reportes.gmemitidos",
            "reportes.r_gmemitidos",
            "reportes.historial_accions",
            "reportes.r_historial_accions",
        ],
        "SECRETARIA" => [
            "sucursals.listado",

            "tipo_certificados.paginado",
            "tipo_certificados.index",
            "tipo_certificados.listado",
            "tipo_certificados.show",

            "clientes.paginado",
            "clientes.index",
            "clientes.listado",
            "clientes.byCi",
            "clientes.create",
            "clientes.store",
            "clientes.nuevo",
            "clientes.edit",
            "clientes.show",
            "clientes.update",
            "clientes.destroy",
            "clientes.eliminados",
            "clientes.restaurar",
            "clientes.eliminacionPermanente",
            "clientes.paginadoEliminados",

            "tramitadors.paginado",
            "tramitadors.index",
            "tramitadors.listado",
            "tramitadors.create",
            "tramitadors.store",
            "tramitadors.edit",
            "tramitadors.show",
            "tramitadors.update",
            "tramitadors.destroy",

            "tipo_pagos.listado",

            "certificados.paginado",
            "certificados.listadoCobros",
            "certificados.index",
            "certificados.listado",
            "certificados.show",
            "certificados.destroy",
            "certificados.registroCliente",
            "certificados.editDetalles",
            "certificados.updateDetalles",
            "certificados.eliminados",
            "certificados.restaurar",
            "certificados.eliminacionPermanente",
            "certificados.paginadoEliminados",

            "certificado_emitidos.verificaCantidad",

            "cobros.index",
            "cobros.registrarPago",

            "recepcion_pagos.index",

            "pagos.index",
            "pagos.verificados",
            "pagos.registrarPagos",

            "certificadosEmitidosLinea",
            "cantidadTramitesNormal",

            "login_users.verificaSucursal",
            "login_users.asignaSucursal",

            "reportes.exportarCaja",
        ],
        "MÉDICO" => [
            "sucursals.listado",

            "tipo_certificados.paginado",
            "tipo_certificados.index",
            "tipo_certificados.listado",
            "tipo_certificados.show",

            "clientes.paginado",
            "clientes.index",
            "clientes.listado",
            "clientes.byCi",
            "clientes.create",
            "clientes.store",
            "clientes.nuevo",
            "clientes.edit",
            "clientes.show",
            "clientes.update",
            "clientes.destroy",
            "certificados.verificaPendienteCliente",
            "clientes.eliminados",
            "clientes.restaurar",
            "clientes.eliminacionPermanente",
            "clientes.paginadoEliminados",

            "tramitadors.paginado",
            "tramitadors.index",
            "tramitadors.listado",
            "tramitadors.show",

            "tipo_pagos.listado",

            "certificados.paginado",
            "certificados.listadoCobros",
            "certificados.index",
            "certificados.listado",
            "certificados.create",
            "certificados.store",
            "certificados.edit",
            "certificados.show",
            "certificados.update",
            "certificados.destroy",
            "certificados.editDetalles",
            "certificados.updateDetalles",
            "certificados.eliminados",
            "certificados.restaurar",
            "certificados.eliminacionPermanente",
            "certificados.paginadoEliminados",

            "certificado_emitidos.verificaCantidad",

            "cobros.index",
            "cobros.registrarPago",

            "pagos.index",
            "pagos.verificados",
            // "pagos.registrarPagos",

            "certificadosEmitidosLinea",
            "cantidadTramitesNormal",

            "login_users.verificaSucursal",
            "login_users.asignaSucursal",

            "reportes.exportarCaja",
            "reportes.r_certificados_diario",
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
