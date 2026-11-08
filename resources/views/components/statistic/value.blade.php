@blaze

@use('Illuminate\Support\Number')

@props([
    'value' => null,
    'precision' => null,
    'prefix' => null,
    'suffix' => null,
])

{{-- The value is grouped for the app's locale (needs PHP's intl extension); content replaces it. --}}
<div {{ $attributes->merge(['data-slot' => 'statistic-value']) }}>{{ $prefix }}@if ($slot->isNotEmpty()){{ $slot }}@elseif ($value !== null){{ Number::format($value, $precision, locale: app()->getLocale()) }}@endif{{ $suffix }}</div>
