@props(['value', 'initialValue', 'min' => 0, 'max' => 100, 'step' => 1, 'orientation' => 'horizontal', 'inverted' => false])
@php
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'slider', get_defined_vars(), ['min' => $min, 'max' => $max, 'step' => $step, 'orientation' => $orientation, 'inverted' => $inverted]);
    $disabled = $binding->config['disabled'];
    $readonly = $binding->config['readonly'];
    $values = is_array($binding->config['value']) ? $binding->config['value'] : [$binding->config['value'] ?? $min];
    sort($values);
@endphp
<div data-slot="slider-field">
    <div data-slot="slider" x-data="uiSlider()"
        data-ui-control="{{ json_encode($binding->config, JSON_THROW_ON_ERROR) }}"
        @if($binding->key) wire:key="{{ $binding->key }}" @endif
        x-on:change="changed($event)" x-on:focusout="blurred($event)" x-on:keydown.enter="entered($event)" data-orientation="{{ $orientation }}" data-inverted="{{ $inverted ? 'true' : 'false' }}"
        x-on:pointerdown="start($event)" x-on:pointermove="move($event)" x-on:pointerup="finish($event)" x-on:pointercancel="finish($event)"
        {{ $attributes->whereDoesntStartWith(['aria-'])->except(['wire:key', 'id', 'name']) }}>
        <div data-slot="slider-track"><div data-slot="slider-range" x-bind:style="rangeStyle()"></div></div>
        @foreach ($values as $index => $number)
            <input type="range" data-slot="slider-thumb" data-slider-index="{{ $index }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}"
                id="{{ $binding->id }}{{ $index ? '-'.$index : '' }}" value="{{ $number }}"
                aria-orientation="{{ $orientation }}" {{ $attributes->whereStartsWith('aria-')->merge(['aria-label' => __('ui::ui.slider_value', ['number' => $index + 1])]) }}
                x-bind:value="positions[{{ $index }}]" x-bind:style="thumbStyle({{ $index }})" x-on:input.stop="change({{ $index }}, $event.target.value)"
                x-on:keydown="key($event, {{ $index }})" @disabled($disabled || $readonly)>
        @endforeach
        @if ($attributes->get('name'))
            @foreach ($values as $index => $number)
                <input type="hidden" name="{{ $attributes->get('name') }}{{ is_array($binding->config['value']) ? '[]' : '' }}" value="{{ $number }}" x-bind:value="positions[{{ $index }}]" @disabled($disabled)>
            @endforeach
        @endif
    </div>
    {{ $slot }}
</div>
