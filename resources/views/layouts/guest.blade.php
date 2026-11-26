<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Staff Login' }} | {{ config('app.name', 'CaféFlow') }}</title>
    <link rel="icon" href="{{ asset('images/cafeflow-logo.png') }}" type="image/png">
    <script>document.documentElement.dataset.theme=localStorage.getItem('cafeflow-theme')||'light';</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-body">
    <div class="page-loader" aria-hidden="true"><span></span></div>
    <button class="icon-button guest-theme theme-toggle" type="button" aria-label="Switch to dark mode" title="Toggle dark mode" data-theme-toggle><x-icon name="moon" class="theme-moon"/><x-icon name="sun" class="theme-sun"/></button>
    <main class="login-shell page-enter">
        <section class="login-intro">
            <a class="brand inverse" href="{{ route('home') }}" aria-label="CaféFlow home"><img src="{{ asset('images/cafeflow-logo.png') }}" alt=""><strong>CaféFlow</strong></a>
            <div class="login-message"><p class="eyebrow">Built for better café shifts</p><h1>Serve quickly.<br>Stay in control.</h1><p>One calm workspace for checkout, stock, customer records, and every transaction your team handles.</p></div>
            <small>Secure access for authorized CaféFlow staff</small>
        </section>
        <section class="login-panel">
            <div class="login-card">{{ $slot }}</div>
        </section>
    </main>
</body>
</html>
