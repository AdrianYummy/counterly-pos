<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CaféFlow | Café sales made simple</title>
    <meta name="description" content="CaféFlow is a focused point-of-sale and inventory workspace for modern cafés.">
    <link rel="icon" href="{{ asset('images/cafeflow-logo.png') }}" type="image/png">
    <script>document.documentElement.dataset.theme=localStorage.getItem('cafeflow-theme')||'light';</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-body">
    <div class="page-loader" aria-hidden="true"><span></span></div>
    <header class="landing-nav">
        <a class="brand" href="{{ route('home') }}" aria-label="CaféFlow home"><img src="{{ asset('images/cafeflow-logo.png') }}" alt=""><strong>CaféFlow</strong></a>
        <nav aria-label="Landing navigation"><a href="#features">Features</a><a href="#workflow">How it works</a></nav>
        <div class="landing-actions"><button class="icon-button theme-toggle" type="button" aria-label="Switch to dark mode" data-theme-toggle><x-icon name="moon" class="theme-moon"/><x-icon name="sun" class="theme-sun"/></button><a class="button primary" href="{{ route('login') }}">Staff Sign In<x-icon name="arrow-right"/></a></div>
    </header>
    <main class="page-enter">
        <section class="hero-section">
            <div class="hero-copy"><span class="hero-kicker">Café operations, flowing smoothly</span><h1>From first order to closing time, keep every shift clear.</h1><p>CaféFlow brings checkout, inventory, customers, receipts, and staff access into one focused workspace made for busy café teams.</p><div class="hero-actions"><a class="button primary hero-cta" href="{{ route('login') }}">Open Staff Workspace<x-icon name="arrow-right"/></a><a class="button secondary" href="#features">Explore Features</a></div><div class="hero-trust"><span><b>Fast</b> checkout</span><span><b>Live</b> stock</span><span><b>Secure</b> roles</span></div></div>
            <div class="hero-visual" aria-label="CaféFlow café emblem"><div class="hero-glow"></div><img src="{{ asset('images/cafeflow-logo.png') }}" alt="CaféFlow coffee cup and leaves logo"><span class="float-card card-one"><x-icon name="cart"/><b>Quick checkout</b><small>Clear totals, fewer steps</small></span><span class="float-card card-two"><x-icon name="box"/><b>Stock at a glance</b><small>Know what needs attention</small></span></div>
        </section>
        <section class="landing-features" id="features"><div class="section-heading"><p class="eyebrow">Everything your shift needs</p><h2>Practical tools without the clutter</h2><p>Designed around the work cafés perform every day.</p></div><div class="feature-grid"><article><span class="feature-icon mint"><x-icon name="cart"/></span><h3>Focused point of sale</h3><p>Find products quickly, confirm quantities, and complete a sale with a clear total.</p></article><article><span class="feature-icon gold"><x-icon name="box"/></span><h3>Reliable inventory</h3><p>See stock status immediately and protect quantities from conflicting updates.</p></article><article><span class="feature-icon coral"><x-icon name="receipt"/></span><h3>Useful receipts</h3><p>Print a clean receipt or send it directly to a named customer by email.</p></article><article><span class="feature-icon blue"><x-icon name="staff"/></span><h3>Role-based access</h3><p>Keep account and inventory controls with administrators while cashiers stay focused.</p></article></div></section>
        <section class="workflow-section" id="workflow"><div><p class="eyebrow">A calmer daily workflow</p><h2>Easy to learn. Ready for a real café shift.</h2></div><ol><li><span>01</span><div><b>Sign in securely</b><p>Each staff member uses their own protected account.</p></div></li><li><span>02</span><div><b>Choose and complete</b><p>Select an available product, customer, and quantity.</p></div></li><li><span>03</span><div><b>Keep a clear record</b><p>Stock updates and the transaction receipt are saved together.</p></div></li></ol></section>
    </main>
    <footer class="landing-footer"><a class="brand" href="{{ route('home') }}"><img src="{{ asset('images/cafeflow-logo.png') }}" alt=""><strong>CaféFlow</strong></a><p>Minimal café management for clearer, faster shifts.</p><a href="{{ route('login') }}">Staff sign in</a></footer>
</body>
</html>
