<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class ChekPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $role_id = Auth::user()->role_id;
            $namedRoute = \Route::currentRouteName();
            $current_url_check = DB::table('menus')->select('menu_url')->where('menu_url', $namedRoute)->get()->toArray();
            if ($namedRoute) {
                if ($current_url_check) {
                    $permissionCheck = DB::table('menus')
                        ->join('permissions', 'permissions.menu_id', '=', 'menus.id')
                        ->where('permissions.role_id', $role_id)
                        ->where('menus.menu_url', $namedRoute)
                        ->exists();

                    if (!$permissionCheck) {
                        abort(403, 'Unauthorized action.');
                    }

                    // Ajustar la consulta para utilizar la columna correcta
                    $side_menu = DB::table('menus')
                        ->join('permissions', 'permissions.menu_id', '=', 'menus.id')
                        ->where('permissions.role_id', $role_id)
                        ->select('menus.menu_url', 'menus.icon', 'menus.name as menu_name') // Usar 'name' en lugar de 'menu_name'
                        ->get()
                        ->toArray();

                    Session::put('side_menu', $side_menu);

                    // Depurar el contenido de side_menu
                    //Log::info('Contenido de side_menu:', $side_menu);
                }
            }
        }

        return $next($request);
    }
}