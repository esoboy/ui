@blaze

@aware([
    'value' => null,
    'min' => 0,
    'max' => 100,
])

{{-- Shows the percentage, like Base UI's default format, unless given content. --}}
<span {{ $attributes->merge(['data-slot' => 'progress-value']) }}>@if ($slot->isEmpty()){{ $value === null ? '' : round(($value - $min) / max($max - $min, 1) * 100).'%' }}@else{{ $slot }}@endif</span>
