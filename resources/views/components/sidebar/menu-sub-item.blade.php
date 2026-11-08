@blaze(fold: true)

<li {{ $attributes->merge(['data-slot' => 'sidebar-menu-sub-item', 'data-sidebar' => 'menu-sub-item']) }}>{{ $slot }}</li>
