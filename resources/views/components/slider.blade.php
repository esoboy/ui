@blaze

@use('Esoboy\Ui\Support\Model')

@props([
    'value' => null,
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'orientation' => 'horizontal',
])

@php
// One thumb per value: an array is a range. Without a default, the bound Livewire value decides.
$values = array_values((array) (Model::value($attributes, $value) ?? $min));
$percent = fn ($value) => $max == $min ? 0 : round(((float) $value - $min) / ($max - $min) * 100, 4);
$start = count($values) > 1 ? $percent(min($values)) : 0;
$end = $percent(max($values));
$thumbAttributes = $attributes->only(['disabled', 'aria-label', 'aria-labelledby']);
@endphp

<eso-slider {{ $attributes->whereDoesntStartWith('wire:model')->except(['name', 'disabled', 'aria-label', 'aria-labelledby'])->merge(['data-slot' => 'slider', 'data-orientation' => $orientation, 'data-disabled' => (bool) $attributes->get('disabled', false), 'x-data' => 'uiSlider('.json_encode(Model::binding($attributes)).')', 'x-init' => '$nextTick(() => paint())', 'x-effect' => 'paint()', 'x-on:input' => 'input($event)', 'x-on:pointerdown' => 'press($event)', 'x-on:pointermove' => 'move($event)', 'x-on:pointerup' => 'end()', 'x-on:pointercancel' => 'end()']) }}>
    <div data-slot="slider-control">
        <div data-slot="slider-track">
            <div data-slot="slider-range" style="--start: {{ $start }}; --end: {{ $end }}"></div>
        </div>
        @foreach ($values as $index => $value)
            <input {{ $thumbAttributes->merge(['type' => 'range', 'data-slot' => 'slider-thumb', 'min' => $min, 'max' => $max, 'step' => $step, 'value' => $value, 'aria-orientation' => $orientation, 'name' => $attributes->has('name') ? $attributes->get('name').(count($values) > 1 ? '[]' : '') : false, ...Model::input($attributes, count($values) > 1 ? $index : null, number: true)->getAttributes()]) }}>
        @endforeach
    </div>
</eso-slider>
