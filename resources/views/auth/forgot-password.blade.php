<x-layouts.guest title="Forgot Password">
    <div class="auth-heading">
        <p class="eyebrow">Account recovery</p>
        <h2>Reset your password</h2>
        <p>Enter the email address assigned to your staff account. We will send a secure link if the account exists.</p>
    </div>
    @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert danger" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.email') }}" class="form-stack">
        @csrf
        <label>Email address<input type="email" name="email" value="{{ old('email') }}" maxlength="150" autocomplete="email" autofocus required></label>
        <button class="button primary wide" type="submit">Send reset link</button>
    </form>
    <a class="auth-back" href="{{ route('login') }}">Back to sign in</a>
</x-layouts.guest>
