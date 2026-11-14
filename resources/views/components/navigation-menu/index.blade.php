@blaze

@props([
    'align' => 'start',
])

<nav {{ $attributes->merge(['data-slot' => 'navigation-menu']) }}>{{ $slot }}</nav>
