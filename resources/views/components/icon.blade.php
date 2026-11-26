@props(['name'])
<svg {{ $attributes->merge(['class' => 'icon', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('dashboard')<path d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z"/>@break
        @case('cart')<path d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L20 7H6"/><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/>@break
        @case('box')<path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/><path d="M12 11v10"/>@break
        @case('users')<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>@break
        @case('receipt')<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6M9 16h3"/>@break
        @case('staff')<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0M19 4v4M17 6h4"/>@break
        @case('search')<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>@break
        @case('plus')<path d="M12 5v14M5 12h14"/>@break
        @case('arrow-left')<path d="m15 18-6-6 6-6"/>@break
        @case('printer')<path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/>@break
        @case('logout')<path d="M10 17l5-5-5-5M15 12H3M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>@break
        @case('menu')<path d="M4 7h16M4 12h16M4 17h16"/>@break
        @case('sidebar')<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16M14 9l-3 3 3 3"/>@break
        @case('moon')<path d="M20.5 14.2A8 8 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z"/>@break
        @case('sun')<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>@break
        @case('arrow-right')<path d="M5 12h14M13 6l6 6-6 6"/>@break
        @case('mail')<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>@break
        @default<circle cx="12" cy="12" r="9"/>@break
    @endswitch
</svg>
