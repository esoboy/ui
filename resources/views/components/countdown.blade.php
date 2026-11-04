@blaze

@use('Esoboy\Ui\Support\Countdown')

@props([
    'until' => null,
    'seconds' => null,
    'name' => null,
    'format' => 'HH:mm:ss',
])

{{-- Counts down to until, or for seconds from when it shows; Ui::countdown($name) starts it again. It renders the time left, so it is right before the script runs, and dispatches finish at zero (wire:finish). --}}
@php
$remaining = Countdown::remaining($until, $seconds);
@endphp

<eso-countdown {{ $attributes->merge([
    'role' => 'timer',
    'data-slot' => 'countdown',
    'data-remaining' => $remaining,
    'data-seconds' => $until === null ? $seconds : null,
    'data-name' => $name,
    'data-format' => $format,
]) }}>{{ Countdown::format($remaining, $format) }}</eso-countdown>
<x-ui::extras />
