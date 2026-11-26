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
        <a class="brand" href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('sales.create') }}"><span>C</span><strong>Counterly</strong><small>Point of Sale</small></a>
        <nav aria-label="Main navigation">
            @if(auth()->user()->isAdmin())<a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><x-icon name="dashboard"/>Dashboard</a>@endif
            <a class="{{ request()->routeIs('sales.create') ? 'active' : '' }}" href="{{ route('sales.create') }}"><x-icon name="cart"/>Point of Sale</a>
            <a class="{{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><x-icon name="box"/>Products & Inventory</a>
            <a class="{{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}"><x-icon name="users"/>Customers</a>
            <a class="{{ request()->routeIs('sales.index', 'sales.show') ? 'active' : '' }}" href="{{ route('sales.index') }}"><x-icon name="receipt"/>Transactions</a>
            @if(auth()->user()->isAdmin())<a class="{{ request()->routeIs('staff.*') ? 'active' : '' }}" href="{{ route('staff.index') }}"><x-icon name="staff"/>User Accounts</a>@endif
        </nav>
        <div class="sidebar-foot"><span class="status-dot"></span>Secure staff workspace</div>
    </aside>
    <div class="page-shell">
        <header class="topbar">
            <button class="menu-toggle" type="button" aria-label="Toggle navigation" aria-controls="sidebar" aria-expanded="false"><x-icon name="menu"/></button>
            <div class="page-title"><p class="eyebrow">Counterly POS</p><h1>{{ $title ?? 'Dashboard' }}</h1></div>
            <div class="account-menu">
                <img src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : asset('images/avatar-placeholder.svg') }}" alt="{{ auth()->user()->full_name }} avatar">
                <div><strong>{{ auth()->user()->full_name }}</strong><small>{{ ucfirst(auth()->user()->role) }} · {{ '@'.auth()->user()->username }}</small></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit"><x-icon name="logout"/>Log out</button></form>
            </div>
        </header>
        <main class="content">
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
