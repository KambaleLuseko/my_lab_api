<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Vérifie si l’utilisateur a ce droit
        $hasPermission = $user->roles()
            ->whereHas('permissions', function ($q) use ($permission) {
                $q->where('name', $permission);
            })->exists();

        if (!$hasPermission) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
    