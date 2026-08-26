<?php

use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\CertificadoEmitidoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobroController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\LoginUserController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecepcionPagoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SincronizacionController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\TipoCertificadoController;
use App\Http\Controllers\TipoPagoController;
use App\Http\Controllers\TipoUsuarioController;
use App\Http\Controllers\TramitadorController;
use App\Http\Controllers\TramiteClienteController;
use App\Http\Controllers\TramiteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('inicio');
    }
    return Inertia::render('Auth/Login');
});

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('inicio');
    }
    return Inertia::render('Auth/Login');
})->name("login");

Route::get("configuracions/getConfiguracion", [ConfiguracionController::class, 'getConfiguracion'])->name("configuracions.getConfiguracion");

Route::get('/clear-cache', function () {
    Artisan::call('config:cache');
    Artisan::call('config:clear');
    Artisan::call('optimize');
    return 'Cache eliminado <a href="/">Ir al inicio</a>';
})->name('clear.cache');

Route::get("sincronizarInicio", [SincronizacionController::class, 'sincronizarInicio']);
Route::get("sincronizarClientesTramitador", [SincronizacionController::class, 'sincronizarClientesTramitador']);

// ADMINISTRACION
Route::middleware(['auth', 'permisoUsuario'])->prefix("admin")->group(function () {
    // INICIO
    Route::get('/inicio', [InicioController::class, 'inicio'])->name('inicio');
    Route::get('/certificadosEmitidosLinea', [InicioController::class, 'certificadosEmitidosLinea'])->name('certificadosEmitidosLinea');
    Route::get('/cantidadTramitesNormal', [InicioController::class, 'cantidadTramitesNormal'])->name('cantidadTramitesNormal');

    // CONFIGURACION
    Route::resource("configuracions", ConfiguracionController::class)->only(
        ["index", "show", "update"]
    );

    // USUARIO
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('profile/update_foto', [ProfileController::class, 'update_foto'])->name('profile.update_foto');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get("getUser", [UserController::class, 'getUser'])->name('users.getUser');
    Route::get("permisosUsuario", [UserController::class, 'permisosUsuario']);

    // USUARIOS
    Route::put("usuarios/password/{user}", [UsuarioController::class, 'actualizaPassword'])->name("usuarios.password");
    Route::get("usuarios/paginado", [UsuarioController::class, 'paginado'])->name("usuarios.paginado");
    Route::get("usuarios/listado", [UsuarioController::class, 'listado'])->name("usuarios.listado");
    Route::get("usuarios/listado/byTipo", [UsuarioController::class, 'byTipo'])->name("usuarios.byTipo");
    Route::get("usuarios/show/{user}", [UsuarioController::class, 'show'])->name("usuarios.show");
    Route::put("usuarios/update/{user}", [UsuarioController::class, 'update'])->name("usuarios.update");
    Route::delete("usuarios/{user}", [UsuarioController::class, 'destroy'])->name("usuarios.destroy");
    Route::resource("usuarios", UsuarioController::class)->only(
        ["index", "store"]
    );

    // LOGIN USER
    Route::get("login_users/verificaSucursal", [LoginUserController::class, 'verificaSucursal'])->name("login_users.verificaSucursal");
    Route::post("login_users/asignaSucursal", [LoginUserController::class, 'asignaSucursal'])->name("login_users.asignaSucursal");

    // TIPO USUARIOS
    Route::get("tipo_usuarios/listado", [TipoUsuarioController::class, 'listado'])->name("tipo_usuarios.listado");

    // SUCURSALES
    Route::get("sucursals/paginado", [SucursalController::class, 'paginado'])->name("sucursals.paginado");
    Route::get("sucursals/listado", [SucursalController::class, 'listado'])->name("sucursals.listado");
    Route::resource("sucursals", SucursalController::class)->only(
        ["index", "store", "edit", "show", "update", "destroy"]
    );

    // CLIENTES
    Route::post("clientes/nuevo", [ClienteController::class, 'nuevo'])->name("clientes.nuevo");
    Route::get("clientes/paginado", [ClienteController::class, 'paginado'])->name("clientes.paginado");
    Route::get("clientes/listado", [ClienteController::class, 'listado'])->name("clientes.listado");
    Route::get("clientes/byCi", [ClienteController::class, 'byCi'])->name("clientes.byCi");
    Route::patch("clientes/restaurar/{cliente}", [ClienteController::class, 'restaurar'])->name("clientes.restaurar");
    Route::delete("clientes/eliminacionPermanente/{cliente}", [ClienteController::class, 'eliminacionPermanente'])->name("clientes.eliminacionPermanente");
    Route::get("clientes/eliminados", [ClienteController::class, 'eliminados'])->name("clientes.eliminados");
    Route::get("clientes/paginadoEliminados", [ClienteController::class, 'paginadoEliminados'])->name("clientes.paginadoEliminados");
    Route::resource("clientes", ClienteController::class)->only(
        ["index", "store", "edit", "show", "update", "destroy"]
    );

    // TRAMITADORES
    Route::post("tramitadors/nuevo", [TramitadorController::class, 'nuevo'])->name("tramitadors.nuevo");
    Route::get("tramitadors/paginado", [TramitadorController::class, 'paginado'])->name("tramitadors.paginado");
    Route::get("tramitadors/listado", [TramitadorController::class, 'listado'])->name("tramitadors.listado");
    Route::get("tramitadors/byCi", [TramitadorController::class, 'byCi'])->name("tramitadors.byCi");
    Route::resource("tramitadors", TramitadorController::class)->only(
        ["index", "store", "edit", "show", "update", "destroy"]
    );

    // TIPO CERTIFICADOS
    Route::get("tipo_certificados/paginado", [TipoCertificadoController::class, 'paginado'])->name("tipo_certificados.paginado");
    Route::get("tipo_certificados/listado", [TipoCertificadoController::class, 'listado'])->name("tipo_certificados.listado");
    Route::resource("tipo_certificados", TipoCertificadoController::class)->only(
        ["index", "store", "edit", "show", "update", "destroy"]
    );

    // CERTIFICADOS
    Route::put("certificados/updateDetalles/{certificado}", [CertificadoController::class, 'updateDetalles'])->name("certificados.updateDetalles");
    Route::patch("certificados/restaurar/{certificado}", [CertificadoController::class, 'restaurar'])->name("certificados.restaurar");
    Route::delete("certificados/eliminacionPermanente/{certificado}", [CertificadoController::class, 'eliminacionPermanente'])->name("certificados.eliminacionPermanente");
    Route::get("certificados/verificaPendienteCliente/{cliente}", [CertificadoController::class, 'verificaPendienteCliente'])->name("certificados.verificaPendienteCliente");
    Route::post("certificados/registroCliente/{cliente}", [CertificadoController::class, 'registroCliente'])->name("certificados.registroCliente");
    Route::get("certificados/paginado", [CertificadoController::class, 'paginado'])->name("certificados.paginado");
    Route::get("certificados/eliminados", [CertificadoController::class, 'eliminados'])->name("certificados.eliminados");
    Route::get("certificados/paginadoEliminados", [CertificadoController::class, 'paginadoEliminados'])->name("certificados.paginadoEliminados");
    Route::get("certificados/listado", [CertificadoController::class, 'listado'])->name("certificados.listado");
    Route::get("certificados/listadoCobros", [CertificadoController::class, 'listadoCobros'])->name("certificados.listadoCobros");
    Route::resource("certificados", CertificadoController::class)->only(
        ["index", "store", "create", "edit", "show", "update", "destroy"]
    );

    // CERTIFICADOS EMITIDOS
    Route::get("certificado_emitidos/verificaCantidad", [CertificadoEmitidoController::class, 'verificaCantidad'])->name("certificado_emitidos.verificaCantidad");

    // TIPO PAGO
    Route::get("tipo_pagos/listado", [TipoPagoController::class, 'listado'])->name("tipo_pagos.listado");

    // COBROS
    Route::get("cobros", [CobroController::class, 'index'])->name("cobros.index");
    Route::post("cobros/registrarPago/{certificado}", [CobroController::class, 'registrarPago'])->name("cobros.registrarPago");

    // RECEPCION PAGOS
    Route::get("recepcion_pagos", [RecepcionPagoController::class, 'index'])->name("recepcion_pagos.index");

    // PAGOS
    Route::get("pagos/verificados", [PagoController::class, 'verificados'])->name("pagos.verificados");
    Route::get("pagos", [PagoController::class, 'index'])->name("pagos.index");
    Route::post("pagos", [PagoController::class, 'registrarPagos'])->name("pagos.registrarPagos");

    // REPORTES
    Route::get('reportes/usuarios', [ReporteController::class, 'usuarios'])->name("reportes.usuarios");
    Route::get('reportes/r_usuarios', [ReporteController::class, 'r_usuarios'])->name("reportes.r_usuarios");

    Route::get("reportes/exportarCaja", [ReporteController::class, 'exportarCaja'])->name("reportes.exportarCaja");

    Route::get('reportes/clientes', [ReporteController::class, 'clientes'])->name("reportes.clientes");
    Route::get('reportes/r_clientes', [ReporteController::class, 'r_clientes'])->name("reportes.r_clientes");

    Route::get('reportes/certificados', [ReporteController::class, 'certificados'])->name("reportes.certificados");
    Route::get('reportes/r_certificados', [ReporteController::class, 'r_certificados'])->name("reportes.r_certificados");

    Route::get('reportes/certificados_interno', [ReporteController::class, 'certificados_interno'])->name("reportes.certificados_interno");
    Route::get('reportes/r_certificados_interno', [ReporteController::class, 'r_certificados_interno'])->name("reportes.r_certificados_interno");

    Route::get('reportes/r_certificados_diario', [ReporteController::class, 'r_certificados_diario'])->name("reportes.r_certificados_diario");

    Route::get('reportes/gcemitidos', [ReporteController::class, 'gcemitidos'])->name("reportes.gcemitidos");
    Route::get('reportes/r_gcemitidos', [ReporteController::class, 'r_gcemitidos'])->name("reportes.r_gcemitidos");

    Route::get('reportes/gmemitidos', [ReporteController::class, 'gmemitidos'])->name("reportes.gmemitidos");
    Route::get('reportes/r_gmemitidos', [ReporteController::class, 'r_gmemitidos'])->name("reportes.r_gmemitidos");

    Route::get('reportes/historial_accions', [ReporteController::class, 'historial_accions'])->name("reportes.historial_accions");
    Route::get('reportes/r_historial_accions', [ReporteController::class, 'r_historial_accions'])->name("reportes.r_historial_accions");
});
require __DIR__ . '/auth.php';
