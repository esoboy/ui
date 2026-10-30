@blaze

@use('Esoboy\Ui\Support\Model')

@props([
    'defaultValue' => null,
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'orientation' => 'horizontal',
])

@php
// One thumb per value: an array is a range. Without a default, the bound Livewire value decides.
$values = array_values((array) ($defaultValue ?? Model::value($attributes) ?? $min));
$percent = fn ($value) => $max == $min ? 0 : round(((float) $value - $min) / ($max - $min) * 100, 4);
$start = count($values) > 1 ? $percent(min($values)) : 0;
$end = $percent(max($values));
$thumbAttributes = $attributes->only(['disabled', 'aria-label', 'aria-labelledby']);
@endphp

<eso-slider {{ $attributes->except(['disabled', 'aria-label', 'aria-labelledby'])->merge(['data-slot' => 'slider', 'data-orientation' => $orientation, 'data-disabled' => $attributes->has('disabled')]) }}>
    <div data-slot="slider-control">
        <div data-slot="slider-track">
            <div data-slot="slider-range" style="--start: {{ $start }}; --end: {{ $end }}"></div>
        </div>
        @foreach ($values as $value)
            <input {{ $thumbAttributes->merge(['type' => 'range', 'data-slot' => 'slider-thumb', 'min' => $min, 'max' => $max, 'step' => $step, 'value' => $value, 'aria-orientation' => $orientation]) }}>
        @endforeach
    </div>
</eso-slider>
