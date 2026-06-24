<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $role = $user->role;

        if (!$role || !$role->permissions) {
            abort(403, 'Akses ditolak: tidak memiliki role');
        }

        // Admin (wildcard *) has all access
        if (in_array('*', $role->permissions)) {
            return $next($request);
        }

        // Check if user has any of the required permissions
        foreach ($permissions as $permission) {
            if (in_array($permission, $role->permissions)) {
                return $next($request);
            }
        }

        abort(403, 'Akses ditolak: tidak memiliki hak akses yang diperlukan');
    }
}
