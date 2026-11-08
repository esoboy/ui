@blaze(fold: true)

<ul {{ $attributes->merge(['data-slot' => 'sidebar-menu', 'data-sidebar' => 'menu']) }}>{{ $slot }}</ul>
