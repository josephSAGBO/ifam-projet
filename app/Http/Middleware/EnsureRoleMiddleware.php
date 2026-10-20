<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  $role  Required role for the route
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        // Accept pipe-separated list: role:direction|enseignant
        $allowed = explode('|', $role);

        foreach ($allowed as $r) {
            if ($user->hasRole(trim($r))) {
                return $next($request);
            }
        }

        \Log::error('403 from EnsureRoleMiddleware', [
            'user_id' => $user->id,
            'user_roles' => $user->roles->pluck('name'),
            'required_role' => $role,
        ]);
        abort(403, "Accès refusé. Rôle requis: $role");
    }
}
