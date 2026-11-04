@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'size' => 'default',
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
@endphp

{{-- As in shadcn: class and style go to the wrapper, everything else to the <select>. --}}
<div {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'native-select-wrapper', 'data-size' => $size]) }}>
    <select {{ $attributes->except(['class', 'style'])->merge(['data-slot' => 'native-select', 'data-size' => $size, ...$state->attributes()]) }}>{{ $slot }}</select>
    <x-ui::icon name="chevron-down" data-slot="native-select-icon" />
</div>
<x-ui::field.error :state="$state" />
