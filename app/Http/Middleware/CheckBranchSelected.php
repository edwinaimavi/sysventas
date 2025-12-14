<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckBranchSelected
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 🔹 Rutas que NO deben ser bloqueadas por este middleware
        //    (pueden entrar incluso sin tener sucursal en sesión)
        if ($request->routeIs([
            'home',                 // dashboard principal
            'admin.branches.*',     // módulo de sucursales completo (index, store, list, etc.)
            'admin.select-branch',  // guardar sucursal vía AJAX
            'logout',               // cerrar sesión
        ])) {
            return $next($request);
        }

        // 🔹 Si NO hay sucursal en sesión, bloqueamos el resto
        if (! session()->has('branch_id')) {

            // Si es petición AJAX / JSON:
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Debes seleccionar una sucursal para continuar.',
                ], 403);
            }

            // Si es petición normal (navegador):
            return redirect()
                ->route('home')
                ->with('error', 'Debes seleccionar una sucursal para continuar.');
        }

        // 🔹 Todo OK, hay sucursal en sesión
        return $next($request);
    }
}
