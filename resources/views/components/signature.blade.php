@blaze

@use('Esoboy\Ui\Assets')
@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- Not in shadcn: a pad to sign on, styled as its Textarea. The value is a data URL, PNG or (smaller) SVG; the ink stays dark in it, whatever the theme. --}}
@props([
    'type' => 'image/png',
    'placeholder' => null,
    'value' => null,
    'disabled' => false,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = Model::value($attributes, $value);
$value = is_string($value) && str_starts_with($value, 'data:image/') ? $value : null;
$name = $attributes->get('name');
@endphp


<eso-signature {{ $attributes->whereDoesntStartWith('wire:model')->except('name')->merge(['data-slot' => 'signature', 'x-data' => 'uiEngine('.json_encode(Model::binding($attributes)).')', 'data-engine' => app(Assets::class)->url('signature.js'), 'data-type' => $type, 'data-value' => $value ?? false, 'data-name' => $name ?? false, 'data-disabled' => (bool) $disabled, ...$state->attributes()]) }}>
    <canvas data-slot="signature-canvas" role="img" aria-label="{{ __('ui::signature.label') }}" wire:ignore></canvas>
    <span data-slot="signature-placeholder" @if ($value) hidden @endif wire:ignore>{{ $placeholder ?? __('ui::signature.placeholder') }}</span>
    <x-ui::button variant="ghost" size="icon-sm" data-signature-clear :aria-label="__('ui::signature.clear')" :disabled="$disabled" :hidden="! $value" wire:ignore><x-ui::icon name="eraser" /></x-ui::button>
    <input type="hidden" x-ref="input" @if ($name) name="{{ $name }}" @endif value="{{ $value }}" {{ Model::input($attributes) }}>
</eso-signature>
<x-ui::field.error :state="$state" />
