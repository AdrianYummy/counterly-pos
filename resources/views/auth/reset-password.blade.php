<x-layouts.guest title="Reset Password">
    <div class="auth-heading">
        <p class="eyebrow">Account recovery</p>
        <h2>Choose a new password</h2>
        <p>Use 10–72 characters with uppercase, lowercase, number, and symbol characters.</p>
    </div>
    @if($errors->any())<div class="alert danger" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.update') }}" class="form-stack">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label>Email address<input type="email" name="email" value="{{ old('email', $email) }}" maxlength="150" autocomplete="email" required></label>
        <label>New password<input type="password" name="password" minlength="10" maxlength="72" autocomplete="new-password" required></label>
        <label>Confirm new password<input type="password" name="password_confirmation" minlength="10" maxlength="72" autocomplete="new-password" required></label>
        <button class="button primary wide" type="submit">Reset password</button>
    </form>
    <a class="auth-back" href="{{ route('login') }}">Back to sign in</a>
</x-layouts.guest>
