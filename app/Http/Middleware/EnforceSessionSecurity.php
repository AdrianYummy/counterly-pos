<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionSecurity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $now = now()->timestamp;
        $session = $request->session();

        if (! (bool) $session->get('security.guard_started', false)) {
            $session->put([
                'security.guard_started' => true,
                'security.last_activity_at' => $now,
                'security.last_heartbeat_at' => $now,
            ]);
        }

        $idleFor = $now - (int) $session->get('security.last_activity_at', $now);
        $heartbeatMissingFor = $now - (int) $session->get('security.last_heartbeat_at', $now);
        $closePendingFor = $now - (int) $session->get('security.pending_close_at', $now);
        $idleLimit = max(60, (int) config('counterly.session_idle_timeout', 900));
        $heartbeatLimit = max(60, (int) config('counterly.session_heartbeat_timeout', 180));
        $closeGrace = max(2, (int) config('counterly.session_close_grace', 10));

        if ($idleFor >= $idleLimit || $heartbeatMissingFor >= $heartbeatLimit || ($session->has('security.pending_close_at') && $closePendingFor >= $closeGrace)) {
            Auth::guard('web')->logout();
            $session->invalidate();
            $session->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your secure session has expired.'], 401);
            }

            return redirect()->route('login')->with('warning', 'Your secure session expired. Please sign in again.');
        }

        if (! $request->routeIs('session.heartbeat')) {
            $session->put([
                'security.last_activity_at' => $now,
                'security.last_heartbeat_at' => $now,
            ]);
        }

        return $next($request);
    }
}
