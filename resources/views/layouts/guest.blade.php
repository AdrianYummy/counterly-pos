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
            <div class="login-message"><p class="eyebrow">Retail workspace</p><h1>Fast sales.<br>Clear inventory.</h1><p>A practical workspace for checkout, stock control, customers, and transaction history.</p></div>
            <small>Authorized staff access only</small>
        </section>
        <section class="login-panel">
            <div class="login-card">{{ $slot }}</div>
        </section>
    </main>
</body>
</html>
