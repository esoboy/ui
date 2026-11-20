@props(['value', 'initialValue', 'min' => 0, 'max' => 100, 'step' => 1, 'orientation' => 'horizontal', 'inverted' => false])
@php
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'slider', get_defined_vars(), ['min' => $min, 'max' => $max, 'step' => $step, 'orientation' => $orientation, 'inverted' => $inverted]);
    $disabled = $binding->config['disabled'];
    $readonly = $binding->config['readonly'];
    $fieldAttributes = \Esoboy\Ui\Support\ControlAttributes::forInput($attributes, ['form', 'list', 'tabindex']);
    $internal = $binding->config['mode'] === 'internal';
    $thumb = $fieldAttributes->merge(['min' => $min, 'max' => $max, 'step' => $step, 'aria-orientation' => $orientation]);
    $config = $binding->config;
    if ($internal) {
        $config += ['thumb' => $thumb->all(), 'id' => $binding->id, 'name' => $attributes->get('name'), 'label' => __('ui::ui.slider_value', ['number' => '{n}'])];
    }
    $values = is_array($binding->config['value']) ? $binding->config['value'] : [$binding->config['value'] ?? $min];
    sort($values);
@endphp
<div data-esoboy-ui data-slot="slider-field">
    <div data-esoboy-ui data-slot="slider" x-data="uiSlider()"
        data-ui-control="{{ json_encode($config, JSON_THROW_ON_ERROR) }}" @if ($internal) wire:ignore.children @endif
        @if($binding->key) wire:key="{{ $binding->key }}" @endif
        x-on:change="changed($event)" x-on:focusout="blurred($event)" x-on:keydown.enter="entered($event)" data-orientation="{{ $orientation }}" data-inverted="{{ $inverted ? 'true' : 'false' }}"
        x-on:pointerdown="start($event)" x-on:pointermove="move($event)" x-on:pointerup="finish($event)" x-on:pointercancel="finish($event)"
        {{ $attributes->except(['wire:key', 'id', 'name', ...array_keys($fieldAttributes->all())]) }}>
        <div data-esoboy-ui data-slot="slider-track"><div data-esoboy-ui data-slot="slider-range" x-bind:style="rangeStyle()"></div></div>
        @foreach ($values as $index => $number)
            <input type="range" data-esoboy-ui data-slot="slider-thumb" data-slider-index="{{ $index }}"
                value="{{ $number }}" {{ $thumb->merge(['id' => $binding->inputId($index), 'aria-label' => __('ui::ui.slider_value', ['number' => $index + 1])]) }}
                @if ($internal) x-effect="syncThumb($el, {{ $index }})" x-bind:disabled="config.disabled || config.readonly" @endif
                x-bind:value="positions[{{ $index }}]" x-bind:style="thumbStyle({{ $index }})" x-on:input.stop="change({{ $index }}, $event.target.value)"
                x-on:keydown="key($event, {{ $index }})" @disabled($disabled || $readonly)>
        @endforeach
        @if ($internal || $attributes->get('name'))
            @foreach ($values as $index => $number)
                <input type="hidden" @if ($attributes->get('name')) name="{{ $attributes->get('name') }}{{ is_array($config['value']) ? '[]' : '' }}" @endif
                    @if ($internal) x-bind:name="config.name ? config.name + (Array.isArray(value) ? '[]' : '') : null" x-bind:form="config.thumb.form ?? null" x-bind:disabled="config.disabled" @endif
                    {{ $attributes->only('form') }} value="{{ $number }}" x-bind:value="positions[{{ $index }}]" @disabled($disabled)>
            @endforeach
        @endif
    </div>
    {{ $slot }}
</div>
