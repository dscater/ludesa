<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Certificado;
use App\Models\Cliente;
use App\Models\Jugador;
use App\Models\LoginUser;
use App\Models\Partido;
use App\Models\User;
use App\Services\LoginUserService;
use App\Services\PermisoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{


    public function __construct(private LoginUserService $login_user_service) {}

    public function permisosUsuario(Request $request)
    {
        $permisoService = new PermisoService();
        return response()->JSON([
            "permisos" => $permisoService->getPermisosUser()
        ]);
    }

    public function getUser()
    {
        return response()->JSON([
            "user" => Auth::user()
        ]);
    }

    public static function getInfoBoxUser()
    {
        $permisos = [];
        $array_infos = [];
        if (Auth::check()) {
            $oUser = new User();
            $permisos = $oUser->permisos;
            if ($permisos == '*' || (is_array($permisos) && in_array('carreras.index', $permisos))) {
                $carreras = Carrera::count();
                $array_infos[] = [
                    'label' => 'CARRERAS',
                    'cantidad' => $carreras,
                    'color' => 'bgWhite',
                    'icon' => "fa-list-alt",
                    "url" => "carreras.index"
                ];
            }

            if ($permisos == '*' || (is_array($permisos) && in_array('partidos.index', $permisos))) {
                $partidos = Partido::where("estado", "PENDIENTE")->count();
                $array_infos[] = [
                    'label' => 'PARTIDOS PENDIENTES',
                    'cantidad' => $partidos,
                    'color' => 'bgWhite',
                    'icon' => "fa-table",
                    "url" => "partidos.index"
                ];
            }


            if ($permisos == '*' || (is_array($permisos) && in_array('jugadors.index', $permisos))) {
                $jugadors = Jugador::count();
                $array_infos[] = [
                    'label' => 'JUGADORES',
                    'cantidad' => $jugadors,
                    'color' => 'bgWhite',
                    'icon' => "fa-user-friends",
                    "url" => "jugadors.index"
                ];
            }
        }


        return $array_infos;
    }
}
