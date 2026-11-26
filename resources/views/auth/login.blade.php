<x-layouts.guest title="Staff Login">
    <div class="auth-heading"><p class="eyebrow">Welcome back</p><h2>Sign in to your shift</h2><p>Enter the username and password assigned to your CaféFlow staff account.</p></div>
    @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert danger" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('login.store') }}" class="form-stack">
        @csrf
        <label>Username<input type="text" name="username" value="{{ old('username') }}" maxlength="50" autocomplete="username" autofocus required></label>
        <label>Password<input type="password" name="password" maxlength="72" autocomplete="current-password" required></label>
        <div class="form-meta"><a href="{{ route('password.request') }}">Forgot password?</a></div>
        <button class="button primary wide" type="submit">Sign in</button>
    </form>
</x-layouts.guest>
