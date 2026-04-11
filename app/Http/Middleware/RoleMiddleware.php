<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        
        // ADMIN a accès à TOUTES les routes
        if ($user->role === 'admin') {
            return $next($request);
        }
        
        // Pour les autres rôles, vérifier l'accès
        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }
        
        abort(403, 'Accès non autorisé. Votre rôle est "' . $user->role . '"');
    }
}