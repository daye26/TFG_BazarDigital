<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Asignamos $user al usuario que está usando el sistema
        $user = $request->user();

        // Comprobamos que el usuario esté loggeado (igual que en CheckAuth)
        if (!$user) {
            return redirect()->route('login');
        }

        $userRole = $user->role instanceof UserRole ? $user->role->value : $user->role;

        if ($userRole !== $role) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
