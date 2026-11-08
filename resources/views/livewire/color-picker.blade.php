@php
    $disabled = (bool) ($settings['disabled'] ?? false);
    $placeholder = $settings['placeholder'] ?? null;
    $name = $attributes->get('name');
@endphp
<eso-color-picker {{ $attributes->whereDoesntStartWith('wire:model')->except(['class', 'style', 'name'])->merge(['data-slot' => 'color-picker', 'x-data' => 'uiColorPicker',
    'x-on:input' => 'previewHue($event)', 'x-on:change' => 'commitHue($event)', 'x-on:keydown' => 'key($event)',
    'x-on:pointerdown' => 'start($event)', 'x-on:pointermove' => 'move($event)',
    'x-on:pointerup' => 'end($event)', 'x-on:pointercancel' => 'cancel()',
    'data-value' => $color ?? false, 'data-name' => $name ?? false, 'data-placeholder' => $placeholder, ...$settings['validation'] ?? []]) }}>
    <x-ui::popover>
        <x-ui::popover.trigger><x-ui::button variant="outline" data-color-picker-trigger :data-empty="$color ? 'false' : 'true'" :disabled="$disabled" :attributes="$attributes->only(['class', 'style'])->merge($settings['validation'] ?? [])">
            <span data-slot="color-picker-swatch" style="--color-picker-color: {{ $color ?? 'transparent' }}"></span>
            <span data-slot="color-picker-value">{{ $color ?? $placeholder }}</span>
        </x-ui::button></x-ui::popover.trigger>
        <x-ui::popover.content align="start" data-color-picker-content>
            <div data-slot="color-picker-area" role="slider" tabindex="0" aria-label="{{ __('ui::color-picker.area') }}">
                <span data-slot="color-picker-thumb"></span>
            </div>
            <div data-slot="color-picker-controls">
                <x-ui::button variant="outline" size="icon-sm" data-color-picker-eyedropper x-on:click="pick()" :aria-label="__('ui::color-picker.eyedropper')" x-bind:hidden="!('EyeDropper' in window)" hidden>
                    <x-ui::icon name="pipette" />
                </x-ui::button>
                <x-ui::slider data-color-picker-hue :min="0" :max="360" :step="1" :aria-label="__('ui::color-picker.hue')" />
            </div>
            <x-ui::input data-color-picker-input wire:change="setColor($event.target.value)" wire:keydown.enter.prevent="setColor($event.target.value)" :value="$color" maxlength="7" spellcheck="false" autocomplete="off" :aria-label="__('ui::color-picker.hex')" />
            @if ($swatches)
            <div data-slot="color-picker-swatches">
                @foreach ($swatches as $swatch)
                <button type="button" data-color-picker-swatch="{{ $swatch }}" style="--color-picker-color: {{ $swatch }}" wire:click="setColor(@js($swatch))" aria-label="{{ $swatch }}" aria-pressed="{{ $swatch === $color ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
            @endif
        </x-ui::popover.content>
    </x-ui::popover>
    <input type="hidden" @if ($name) name="{{ $name }}" @endif value="{{ $color }}">
</eso-color-picker>
