@blaze(fold: true)

<ul {{ $attributes->merge(['data-slot' => 'navigation-menu-list']) }}>{{ $slot }}</ul>
