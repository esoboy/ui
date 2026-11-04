@blaze

{{-- variant colours the dot: default, success, warning, info or destructive. --}}
@props([
    'variant' => 'default',
])

<li {{ $attributes->merge(['data-slot' => 'timeline-item', 'data-variant' => $variant]) }}>
    <span data-slot="timeline-indicator" aria-hidden="true">@isset ($icon){{ $icon }}@endisset</span>
    <div data-slot="timeline-content">{{ $slot }}</div>
</li>
