@blaze(fold: true)

<li {{ $attributes->merge(['data-slot' => 'navigation-menu-item']) }}>{{ $slot }}</li>
