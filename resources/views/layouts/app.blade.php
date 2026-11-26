<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} | {{ config('app.name', 'Counterly POS') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('dashboard') }}"><span>C</span><strong>Counterly</strong></a>
        <nav aria-label="Main navigation">
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Products</a>
            <a class="{{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">Customers</a>
            <a class="{{ request()->routeIs('staff.*') ? 'active' : '' }}" href="{{ route('staff.index') }}">Staff Accounts</a>
            <a class="{{ request()->routeIs('sales.create') ? 'active' : '' }}" href="{{ route('sales.create') }}">Record Sale</a>
            <a class="{{ request()->routeIs('sales.index', 'sales.show') ? 'active' : '' }}" href="{{ route('sales.index') }}">Sales History</a>
        </nav>
        <div class="sidebar-foot">Secure staff workspace</div>
    </aside>
    <div class="page-shell">
        <header class="topbar">
            <button class="menu-toggle" type="button" aria-label="Toggle navigation" aria-controls="sidebar" aria-expanded="false">☰</button>
            <div><p class="eyebrow">Counterly POS</p><h1>{{ $title ?? 'Dashboard' }}</h1></div>
            <div class="account-menu">
                <img src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : asset('images/avatar-placeholder.svg') }}" alt="{{ auth()->user()->full_name }} avatar">
                <div><strong>{{ auth()->user()->full_name }}</strong><small>{{ '@'.auth()->user()->username }}</small></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Log out</button></form>
            </div>
        </header>
        <main class="content">
            @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert danger" role="alert">{{ session('error') }}</div>@endif
            @if($errors->any())
                <div class="alert danger" role="alert"><strong>Please correct the highlighted fields.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
