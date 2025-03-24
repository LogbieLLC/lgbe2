<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PerformanceDashboardAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has access to the performance dashboard
        $allowedRoles = config('performance.dashboard_access.roles', []);
        $allowedEmails = config('performance.dashboard_access.emails', []);

        $hasAccess = false;

        // Check if user's email is in the allowed list
        if (in_array($user->email, $allowedEmails)) {
            $hasAccess = true;
        }

        // Check if user has an allowed role
        // This assumes your app has a role system - adjust as needed
        if (method_exists($user, 'hasRole')) {
            foreach ($allowedRoles as $role) {
                if ($user->hasRole($role)) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        // For development, allow all authenticated users if no specific roles/emails are configured
        if (empty($allowedRoles) && empty($allowedEmails)) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            abort(403, 'You do not have access to the performance dashboard.');
        }

        return $next($request);
    }
}
