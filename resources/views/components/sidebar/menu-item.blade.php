@blaze(fold: true)

<li {{ $attributes->merge(['data-slot' => 'sidebar-menu-item', 'data-sidebar' => 'menu-item']) }}>{{ $slot }}</li>
