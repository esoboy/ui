@props(['value', 'initialValue'])
@php
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'signature', get_defined_vars(), [
        'maxStrokes' => \Esoboy\Ui\Support\DrawingValue::MAX_STROKES,
        'maxPoints' => \Esoboy\Ui\Support\DrawingValue::MAX_POINTS,
    ]);
    $strokes = \Esoboy\Ui\Support\DrawingValue::signature($binding->config['value']);
@endphp
<div data-slot="signature-field">
    <div data-slot="signature" x-data="uiSignature" data-ui-control="{{ json_encode($binding->config, JSON_THROW_ON_ERROR) }}"
        @if($binding->key) wire:key="{{ $binding->key }}" @endif
        x-on:change="changed($event)" x-on:focusout="blurred($event)" x-on:keydown.enter="entered($event)"
        {{ $attributes->except(['wire:key', 'name'])->merge(['id' => $binding->id]) }}>
        <div data-ui-signature-surface>
            <canvas wire:ignore aria-label="{{ $attributes->get('aria-label', __('ui::ui.signature')) }}"
                x-on:pointerdown.prevent="start($event)" x-on:pointermove.prevent="move($event)"
                x-on:pointerup="finish($event)" x-on:pointercancel="cancel($event)" x-on:lostpointercapture="cancel($event)"
                x-bind:aria-disabled="config.disabled || config.readonly"></canvas>
        </div>
        <x-ui::button variant="outline" x-on:click="clear()" :disabled="$binding->config['disabled'] || $binding->config['readonly']"
            x-bind:disabled="config.disabled || config.readonly">{{ __('ui::ui.clear') }}</x-ui::button>
        @if ($attributes->get('name'))
            <input type="hidden" name="{{ $attributes->get('name') }}" value="{{ json_encode($strokes) }}"
                x-bind:value="JSON.stringify(value ?? [])" @disabled($binding->config['disabled'])>
        @endif
    </div>
    {{ $slot }}
</div>
