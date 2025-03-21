<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectSuperAdminStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Prevent modification of is_super_admin field through requests
        if ($request->has('is_super_admin')) {
            $request->request->remove('is_super_admin');
        }
        
        return $next($request);
    }
}
