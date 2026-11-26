<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SessionSecurityController extends Controller
{
    public function heartbeat(Request $request): Response
    {
        $request->session()->put('security.last_heartbeat_at', now()->timestamp);

        return response()->noContent();
    }

    public function activity(Request $request): Response
    {
        $now = now()->timestamp;
        $request->session()->put([
            'security.last_activity_at' => $now,
            'security.last_heartbeat_at' => $now,
        ]);

        return response()->noContent();
    }

    public function close(Request $request): Response
    {
        if ($request->boolean('force')) {
            $this->invalidate($request);

            return response()->noContent();
        }

        $token = $request->input('token');

        if (is_string($token) && $token !== '') {
            $resumedAt = (int) $request->session()->get('security.resumed_close_at', 0);
            $withinGrace = now()->timestamp - $resumedAt <= max(2, (int) config('counterly.session_close_grace', 10));
            if ($withinGrace && hash_equals((string) $request->session()->get('security.resumed_close_token', ''), $token)) {
                $request->session()->forget(['security.resumed_close_token', 'security.resumed_close_at']);

                return response()->noContent();
            }

            $request->session()->put([
                'security.pending_close_token' => $token,
                'security.pending_close_at' => now()->timestamp,
            ]);

            return response()->noContent();
        }

        return response()->noContent();
    }

    public function resume(Request $request): Response
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:100'],
        ]);
        $session = $request->session();
        $token = $data['token'];

        if (hash_equals((string) $session->get('security.pending_close_token', ''), $token)) {
            $pendingAt = (int) $session->get('security.pending_close_at', 0);
            if (now()->timestamp - $pendingAt > max(2, (int) config('counterly.session_close_grace', 10))) {
                $this->invalidate($request);

                return response()->noContent(401);
            }

            $session->forget(['security.pending_close_token', 'security.pending_close_at']);
        } else {
            $session->put([
                'security.resumed_close_token' => $token,
                'security.resumed_close_at' => now()->timestamp,
            ]);
        }

        $session->put('security.last_heartbeat_at', now()->timestamp);

        return response()->noContent();
    }

    private function invalidate(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
