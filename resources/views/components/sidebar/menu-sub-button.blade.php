@blaze(fold: true, unsafe: ['href'])

@props([
    'active' => false,
    'size' => 'md',
])

<a {{ $attributes->merge(['data-slot' => 'sidebar-menu-sub-button', 'data-sidebar' => 'menu-sub-button', 'data-size' => $size, 'data-active' => $active]) }}>{{ $slot }}</a>
