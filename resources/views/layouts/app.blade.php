<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} | {{ config('app.name', 'CaféFlow') }}</title>
    <link rel="icon" href="{{ asset('images/cafeflow-logo.png') }}" type="image/png">
    <script>document.documentElement.dataset.theme=localStorage.getItem('cafeflow-theme')||'light';</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="page-loader" aria-hidden="true"><span></span></div>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('sales.create') }}" aria-label="CaféFlow home"><img src="{{ asset('images/cafeflow-logo.png') }}" alt=""><strong>CaféFlow</strong></a>
        <nav aria-label="Main navigation">
            @if(auth()->user()->isAdmin())<a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><x-icon name="dashboard"/><span>Dashboard</span></a>@endif
            <a class="{{ request()->routeIs('sales.create') ? 'active' : '' }}" href="{{ route('sales.create') }}"><x-icon name="cart"/><span>Point of Sale</span></a>
            <a class="{{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><x-icon name="box"/><span>Products & Inventory</span></a>
            <a class="{{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}"><x-icon name="users"/><span>Customers</span></a>
            <a class="{{ request()->routeIs('sales.index', 'sales.show') ? 'active' : '' }}" href="{{ route('sales.index') }}"><x-icon name="receipt"/><span>Transactions</span></a>
            @if(auth()->user()->isAdmin())<a class="{{ request()->routeIs('staff.*') ? 'active' : '' }}" href="{{ route('staff.index') }}"><x-icon name="staff"/><span>User Accounts</span></a>@endif
        </nav>
        <div class="sidebar-foot"><span class="status-dot"></span><span>Secure workspace</span></div>
    </aside>
    <div class="page-shell">
        <header class="topbar">
            <button class="menu-toggle" type="button" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false"><x-icon name="menu"/></button>
            <button class="sidebar-toggle" type="button" aria-label="Minimize sidebar" title="Minimize sidebar" data-sidebar-toggle><x-icon name="sidebar"/></button>
            <div class="page-title"><p class="eyebrow">CaféFlow workspace</p><h1>{{ $title ?? 'Dashboard' }}</h1></div>
            <div class="topbar-tools">
                <button class="icon-button theme-toggle" type="button" aria-label="Switch to dark mode" title="Toggle dark mode" data-theme-toggle><x-icon name="moon" class="theme-moon"/><x-icon name="sun" class="theme-sun"/><span>Theme</span></button>
                <div class="account-menu">
                    <img src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : asset('images/avatar-placeholder.svg') }}" alt="{{ auth()->user()->full_name }} avatar">
                    <div><strong>{{ auth()->user()->full_name }}</strong><small>{{ ucfirst(auth()->user()->role) }} · {{ '@'.auth()->user()->username }}</small></div>
                    <form method="POST" action="{{ route('logout') }}" data-confirm="Sign out of CaféFlow? Any unsaved form changes will be lost.">@csrf<button class="link-button" type="submit"><x-icon name="logout"/>Sign out</button></form>
                </div>
            </div>
        </header>
        <main class="content page-enter">
            @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
            @if(session('warning'))<div class="alert warning" role="status">{{ session('warning') }}</div>@endif
            @if(session('error'))<div class="alert danger" role="alert">{{ session('error') }}</div>@endif
            @if($errors->any())
                <div class="alert danger" role="alert"><strong>Please correct the highlighted fields.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
<dialog class="confirm-dialog" id="confirm-dialog" aria-labelledby="confirm-title">
    <form method="dialog">
        <div class="dialog-icon" aria-hidden="true">!</div>
        <h2 id="confirm-title">Confirm action</h2>
        <p data-confirm-message>This action cannot be undone.</p>
        <div class="dialog-actions"><button class="button secondary" value="cancel">Cancel</button><button class="button danger" value="confirm">Confirm</button></div>
    </form>
</dialog>
</body>
</html>
