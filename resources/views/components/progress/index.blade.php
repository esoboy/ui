@blaze

@props([
    'value' => null,
    'min' => 0,
    'max' => 100,
    'variant' => 'default',
])

@php
$percent = $value === null ? null : max(0, min(100, ($value - $min) / max($max - $min, 1) * 100));
@endphp

{{-- The circle variant draws its track and indicator as rings and centres its content (a Progress Value) in them. --}}
<div {{ $attributes->merge([
    'role' => 'progressbar',
    'aria-valuemin' => $min,
    'aria-valuemax' => $max,
    'aria-valuenow' => $value ?? false,
    'aria-valuetext' => $percent === null ? false : round($percent).'%',
    'data-slot' => 'progress',
    'data-variant' => $variant,
    'data-indeterminate' => $value === null,
    'data-complete' => $percent !== null && $percent >= 100,
]) }}>
    {{ $slot }}
    @if ($variant === 'circle')
    <svg data-slot="progress-circle">
        <circle data-slot="progress-circle-track" />
        <circle data-slot="progress-circle-indicator" pathLength="100" @if ($percent !== null) style="stroke-dashoffset: {{ 100 - $percent }}" @endif />
    </svg>
    @else
    <div data-slot="progress-track">
        <div data-slot="progress-indicator" @if ($percent !== null) style="inset-inline-start: 0; width: {{ $percent }}%" @endif></div>
    </div>
    @endif
</div>
