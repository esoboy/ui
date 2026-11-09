@blaze(fold: true)

@props([
    'level' => 1,
])

{{-- level 2 and 3 indent a subsection's link under its section's. --}}
<a {{ $attributes->merge(['data-slot' => 'anchor-item', 'data-level' => $level]) }}>{{ $slot }}</a>
