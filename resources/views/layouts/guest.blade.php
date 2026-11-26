<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Staff Login' }} | {{ config('app.name', 'Counterly POS') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-body">
    <main class="login-shell">
        <section class="login-intro">
            <div class="brand inverse"><span>C</span><strong>Counterly</strong></div>
            <p class="eyebrow">Smart stock. Clear sales.</p>
            <h1>A focused point-of-sale workspace for everyday business.</h1>
            <p>Manage inventory, customers, staff, and sales from one secure place.</p>
        </section>
        <section class="login-panel">
            <div class="login-card">
                <p class="eyebrow">Welcome back</p>
                <h2>Staff sign in</h2>
                <p class="muted">Use your Counterly staff credentials.</p>
                @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="alert danger" role="alert">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('login.store') }}" class="form-stack">
                    @csrf
                    <label>Username<input type="text" name="username" value="{{ old('username') }}" maxlength="50" autocomplete="username" autofocus required></label>
                    <label>Password<input type="password" name="password" maxlength="72" autocomplete="current-password" required></label>
                    <button class="button primary wide" type="submit">Sign in</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
