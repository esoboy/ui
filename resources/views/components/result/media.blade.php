@blaze

@aware([
    'status' => 'info',
])

@props([
    'variant' => null,
])

{{-- Error pages show their status code; the other statuses a tinted icon. --}}
@php
$code = in_array((string) $status, ['403', '404', '500'], true);
$icon = match ((string) $status) {
    'success' => 'circle-check',
    'error' => 'circle-x',
    'warning' => 'triangle-alert',
    default => 'info',
};
@endphp

<div {{ $attributes->merge(['data-slot' => 'result-media', 'data-variant' => $variant ?? ($code ? 'code' : 'icon')]) }}>@if ($slot->isNotEmpty()){{ $slot }}@elseif ($code){{ $status }}@else<x-ui::icon :name="$icon" />@endif</div>
