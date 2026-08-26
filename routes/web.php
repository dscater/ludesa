<?php

use App\Http\Controllers\CampeonatoController;
use App\Http\Controllers\CampeonatoInscripcionController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SincronizacionController;
use App\Http\Controllers\TipoUsuarioController;
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


    // TIPO USUARIOS
    Route::get("tipo_usuarios/listado", [TipoUsuarioController::class, 'listado'])->name("tipo_usuarios.listado");

    // CAMPEONATOS
    Route::get("campeonatos/paginado", [CampeonatoController::class, 'paginado'])->name("campeonatos.paginado");
    Route::get("campeonatos/listado", [CampeonatoController::class, 'listado'])->name("campeonatos.listado");
    Route::resource("campeonatos", CampeonatoController::class)->only(
        ["index", "store", "edit", "show", "update", "destroy"]
    );

    // CARRERAS
    Route::get("carreras/paginado", [CarreraController::class, 'paginado'])->name("carreras.paginado");
    Route::get("carreras/listado", [CarreraController::class, 'listado'])->name("carreras.listado");
    Route::resource("carreras", CarreraController::class)->only(
        ["index", "store", "edit", "show", "update", "destroy"]
    );

    // CAMPEONATO INSCRIPCIONS
    Route::get("campeonato_inscripcions/paginado", [CampeonatoInscripcionController::class, 'paginado'])->name("campeonato_inscripcions.paginado");
    Route::get("campeonato_inscripcions/listado", [CampeonatoInscripcionController::class, 'listado'])->name("campeonato_inscripcions.listado");
    Route::resource("campeonato_inscripcions", CampeonatoInscripcionController::class)->only(
        ["index", "store", "edit", "show", "update", "destroy"]
    );


    // REPORTES
    Route::get('reportes/usuarios', [ReporteController::class, 'usuarios'])->name("reportes.usuarios");
    Route::get('reportes/r_usuarios', [ReporteController::class, 'r_usuarios'])->name("reportes.r_usuarios");
});
require __DIR__ . '/auth.php';
