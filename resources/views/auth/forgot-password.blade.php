<x-layouts.guest title="Forgot Password">
    <div class="auth-heading">
        <p class="eyebrow">Account recovery</p>
        <h2>Reset your password</h2>
        <p>Enter the recovery email assigned to your staff account. The secure link is valid for 60 minutes.</p>
    </div>
    @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="alert warning" role="alert">{{ session('warning') }}</div>@endif
    @if(session('reset_preview_url'))<div class="mail-preview"><strong>Local mail preview</strong><p>Email delivery is set to the log mailer. Use this link to test password recovery now.</p><a class="button secondary wide" href="{{ session('reset_preview_url') }}">Open Reset Link</a></div>@endif
    @if($errors->any())<div class="alert danger" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.email') }}" class="form-stack">
        @csrf
        <label>Email address<input type="email" name="email" value="{{ old('email') }}" maxlength="150" autocomplete="email" autofocus required></label>
        <button class="button primary wide" type="submit">Send reset link</button>
    </form>
    <a class="auth-back" href="{{ route('login') }}">Back to sign in</a>
</x-layouts.guest>
