@blaze(fold: true)

<ul {{ $attributes->merge(['data-slot' => 'sidebar-menu-sub', 'data-sidebar' => 'menu-sub']) }}>{{ $slot }}</ul>
