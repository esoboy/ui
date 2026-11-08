@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'sidebar-menu-badge', 'data-sidebar' => 'menu-badge']) }}>{{ $slot }}</div>
