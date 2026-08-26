<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use App\Models\Cliente;
use App\Models\LoginUser;
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
            if ($permisos == '*' || (is_array($permisos) && in_array('usuarios.index', $permisos))) {
                $array_infos[] = [
                    'label' => 'USUARIOS',
                    'cantidad' => User::where('id', '!=', 1)->count(),
                    'color' => 'bgWhite',
                    'icon' => "fa-users",
                    "url" => "usuarios.index"
                ];
            }

            $array_infos[] = [
                'label' => 'CARRERAS',
                'cantidad' => 0,
                'color' => 'bgWhite',
                'icon' => "fa-list",
                "url" => "usuarios.index"
            ];

            $array_infos[] = [
                'label' => 'PARTIDOS PENDIENTES',
                'cantidad' => 0,
                'color' => 'bgWhite',
                'icon' => "fa-table",
                "url" => "usuarios.index"
            ];
        }


        return $array_infos;
    }
}
