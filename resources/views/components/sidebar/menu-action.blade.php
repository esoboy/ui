@blaze(fold: true)

@props([
    'showOnHover' => false,
])

<button {{ $attributes->merge(['type' => 'button', 'data-slot' => 'sidebar-menu-action', 'data-sidebar' => 'menu-action', 'data-show-on-hover' => $showOnHover]) }}>{{ $slot }}</button>
