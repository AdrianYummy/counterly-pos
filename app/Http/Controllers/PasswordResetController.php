<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function request(): View
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:150']]);

        $previewUrl = null;

        try {
            if (config('counterly.mail_preview') && config('mail.default') === 'log') {
                $user = User::query()->where('email', $data['email'])->first();
                if ($user) {
                    $token = Password::broker()->createToken($user);
                    $user->sendPasswordResetNotification($token);
                    $previewUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
                }
            } else {
                Password::sendResetLink($data);
            }
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('warning', 'The mail service is not configured correctly. Your administrator should verify the SMTP settings.');
        }

        return back()
            ->with('success', 'If that email belongs to a staff account, a password-reset link has been sent.')
            ->with('reset_preview_url', $previewUrl);
    }

    public function reset(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'password' => ['required', 'string', PasswordRule::min(10)->mixedCase()->numbers()->symbols(), 'max:72', 'confirmed'],
        ]);

        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => $password])->save();
            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
        }

        return redirect()->route('login')->with('success', 'Your password has been reset. You can now sign in.');
    }
}
