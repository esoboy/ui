@blaze

@use('Illuminate\Support\Number')

@props([
    'value' => 0,
    'variant' => 'outline',
])

{{-- The trend badge of shadcn's dashboard cards: the value's sign picks the icon, content replaces the percentage. --}}
@php
$direction = $value > 0 ? 'up' : ($value < 0 ? 'down' : 'flat');
@endphp

<x-ui::badge :variant="$variant" :attributes="$attributes->merge(['data-direction' => $direction])">
    <x-ui::icon :name="match ($direction) { 'up' => 'trending-up', 'down' => 'trending-down', default => 'minus' }" />
    @if ($slot->isNotEmpty()){{ $slot }}@else{{ ($value > 0 ? '+' : '').Number::percentage($value, maxPrecision: 2, locale: app()->getLocale()) }}@endif
</x-ui::badge>
