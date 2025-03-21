<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && $request->route('community')) {
            $community = $request->route('community');
            $user = auth()->user();

            // Check if the user is banned from this community
            $ban = $community->bans()->where('user_id', $user->id)->first();

            if ($ban && $ban->isActive()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'You are banned from this community',
                        'reason' => $ban->reason,
                        'expires_at' => $ban->expires_at,
                    ], 403);
                }

                return redirect()->route('home')
                    ->with('error', 'You are banned from this community: ' . $ban->reason);
            }
        }

        return $next($request);
    }
}
