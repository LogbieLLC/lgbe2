<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is a super admin
        if ($request->user() && $request->user()->is_super_admin) {
            return $next($request);
        }

        // If not a super admin, return unauthorized response
        return response()->json(['message' => 'Unauthorized. Super admin access required.'], 403);
    }
}
