@blaze(fold: true, unsafe: ['href'])

@props([
    'active' => false,
])

{{-- Directly in an item (not in content) it is styled like a trigger, as shadcn does with navigationMenuTriggerStyle(). --}}
<a {{ $attributes->merge(['data-slot' => 'navigation-menu-link', 'data-active' => $active, 'aria-current' => $active ? 'page' : false]) }}>{{ $slot }}</a>
