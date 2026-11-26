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
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
