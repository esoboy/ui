@blaze(fold: true, unsafe: ['href'])

@props([
    'isActive' => false,
    'size' => 'md',
])

<a {{ $attributes->merge(['data-slot' => 'sidebar-menu-sub-button', 'data-sidebar' => 'menu-sub-button', 'data-size' => $size, 'data-active' => $isActive]) }}>{{ $slot }}</a>
