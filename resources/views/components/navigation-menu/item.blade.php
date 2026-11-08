@blaze

@props(['uiNavigationId' => null, 'uiModel' => null])

<li {{ $attributes->merge(['data-slot' => 'navigation-menu-item']) }}>{{ $slot }}</li>
