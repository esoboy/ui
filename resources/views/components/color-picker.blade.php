@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- Not in shadcn: a Popover with a saturation/brightness area, a hue slider, a hex Input and swatches. --}}
@props([
    'placeholder' => null,
    'defaultValue' => null,
    'swatches' => [],
    'disabled' => false,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$hex = strtolower(ltrim((string) ($defaultValue ?? Model::value($attributes)), '#'));
$hex = strlen($hex) === 3 ? preg_replace('/(.)/', '$1$1', $hex) : $hex;
$value = preg_match('/^[0-9a-f]{6}$/', $hex) ? "#{$hex}" : null;
$name = $attributes->get('name');
@endphp

{{-- <eso-color-picker> exposes the color as .value ("#rrggbb" or null), so wire:model binds on it; class styles the trigger. --}}
<eso-color-picker {{ $attributes->except(['class', 'style', 'name'])->merge(['data-slot' => 'color-picker', 'data-value' => $value ?? false, 'data-name' => $name ?? false, 'data-placeholder' => $placeholder, ...$state->attributes()]) }}>
    <x-ui::popover>
        <x-ui::popover.trigger variant="outline" data-color-picker-trigger :data-empty="$value ? 'false' : 'true'" :disabled="$disabled" :attributes="$attributes->only(['class', 'style'])">
            <span data-slot="color-picker-swatch" style="--color-picker-color: {{ $value ?? 'transparent' }}"></span>
            <span data-slot="color-picker-value">{{ $value ?? $placeholder }}</span>
        </x-ui::popover.trigger>
        <x-ui::popover.content align="start" data-color-picker-content>
            <div data-slot="color-picker-area" role="slider" tabindex="0" aria-label="{{ __('ui::color-picker.area') }}">
                <span data-slot="color-picker-thumb"></span>
            </div>
            <div data-slot="color-picker-controls">
                <x-ui::button variant="outline" size="icon-sm" data-color-picker-eyedropper :aria-label="__('ui::color-picker.eyedropper')" hidden>
                    <x-ui::icon name="pipette" />
                </x-ui::button>
                <input type="range" data-slot="color-picker-hue" min="0" max="360" step="1" aria-label="{{ __('ui::color-picker.hue') }}">
            </div>
            <input type="text" data-slot="input" data-color-picker-input value="{{ $value }}" maxlength="7" spellcheck="false" autocomplete="off" aria-label="{{ __('ui::color-picker.hex') }}">
            @if ($swatches)
            <div data-slot="color-picker-swatches">
                @foreach ($swatches as $swatch)
                <button type="button" data-color-picker-swatch="{{ $swatch }}" style="--color-picker-color: {{ $swatch }}" aria-label="{{ $swatch }}" aria-pressed="{{ strtolower($swatch) === $value ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
            @endif
        </x-ui::popover.content>
    </x-ui::popover>
    @if ($name)
    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endif
</eso-color-picker>
<x-ui::field.error :state="$state" />
<x-ui::extras />
