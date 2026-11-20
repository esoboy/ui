@props(['value', 'initialValue', 'src', 'alt' => ''])
@php
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'cropper', get_defined_vars(), []);
    $rect = \Esoboy\Ui\Support\DrawingValue::crop($binding->config['value']);
@endphp
<div data-esoboy-ui data-slot="cropper-field">
    <div data-esoboy-ui data-slot="cropper" x-data="uiCropper" data-ui-control="{{ json_encode($binding->config, JSON_THROW_ON_ERROR) }}"
        @if($binding->key) wire:key="{{ $binding->key }}" @endif
        x-on:change="changed($event)" x-on:focusout="blurred($event)" x-on:keydown.enter="entered($event)"
        {{ $attributes->except(['wire:key', 'name'])->merge(['id' => $binding->id]) }}>
        <div data-ui-crop-surface x-on:pointerdown.prevent="start($event)" x-on:pointermove="move($event)"
            x-on:pointerup="finish($event)" x-on:pointercancel="cancel($event)" x-on:lostpointercapture="cancel($event)">
            <x-ui::image :src="$src" :alt="$alt" draggable="false" />
            <div data-ui-crop-frame role="group" tabindex="0" aria-label="{{ __('ui::ui.crop') }}"
                x-bind:style="cropStyle()"
                :aria-disabled="config.disabled || config.readonly" :tabindex="config.disabled ? -1 : 0"
                style="left:{{ $rect['x'] * 100 }}%;top:{{ $rect['y'] * 100 }}%;width:{{ $rect['width'] * 100 }}%;height:{{ $rect['height'] * 100 }}%"
                x-on:keydown.left.prevent="nudge(-1, 0)" x-on:keydown.right.prevent="nudge(1, 0)"
                x-on:keydown.up.prevent="nudge(0, -1)" x-on:keydown.down.prevent="nudge(0, 1)"><span data-ui-crop-resize></span></div>
        </div>
        @if ($attributes->hasAny(['name', 'form']))
            <input type="hidden" {{ $attributes->only(['name', 'form']) }} value="{{ json_encode($rect) }}"
                x-bind:value="JSON.stringify(rectangle)" @disabled($binding->config['disabled'])>
        @endif
    </div>
    {{ $slot }}
</div>
