@php
    $disabled = $config['disabled'];
    $readonly = $config['readonly'];
    $orientation = $config['orientation'];
    $inverted = $config['inverted'];
    $fieldAttributes = \Esoboy\Ui\Support\ControlAttributes::forInput($attributes, ['form', 'list', 'tabindex']);
    $thumb = $fieldAttributes->merge(['min' => $config['min'], 'max' => $config['max'], 'step' => $config['step'], 'aria-orientation' => $orientation]);
    $values = is_array($config['value']) ? $config['value'] : [$config['value'] ?? $config['min']];
    sort($values);
@endphp
    <div x-data="uiSlider()"
        x-effect="$nextTick(() => configure(@js($config)))" data-ui-control="{{ json_encode($config, JSON_THROW_ON_ERROR) }}"
        @if($controlKey) wire:key="{{ $controlKey }}" @endif
        x-on:change="changed($event)" x-on:focusout="blurred($event)" x-on:keydown.enter="entered($event)" data-orientation="{{ $orientation }}" data-inverted="{{ $inverted ? 'true' : 'false' }}"
        x-on:pointerdown="start($event)" x-on:pointermove="move($event)" x-on:pointerup="finish($event)" x-on:pointercancel="finish($event)"
        {{ $attributes->except(['class', 'wire:key', 'id', 'name', ...array_keys($fieldAttributes->all())])->class('ui-slider-body') }}>
        <div class="ui-slider-track">
            <div class="ui-slider-range" x-bind:style="{
                '--ui-slider-position': positions.length > 1 ? offset(0, true) : '0%',
                '--ui-slider-end': offset(positions.length - 1, true)
            }"></div>
        </div>
        @foreach ($values as $index => $number)
            <input wire:key="thumb-{{ $index }}" class="ui-slider-thumb" type="range" data-slider-index="{{ $index }}"
                value="{{ $number }}" {{ $thumb->merge(['id' => ($index && $inputId ? $inputId.'-'.$index : $inputId), 'aria-label' => __('ui::ui.slider_value', ['number' => $index + 1])]) }}
                x-bind:value="positions[{{ $index }}]" x-bind:style="{ '--ui-slider-position': offset({{ $index }}) }" x-on:input.stop="change({{ $index }}, $event.target.value)"
                x-on:keydown="key($event, {{ $index }})" @disabled($disabled || $readonly)>
        @endforeach
        @if ($attributes->get('name'))
            @foreach ($values as $index => $number)
                <input wire:key="form-thumb-{{ $index }}" type="hidden" @if ($attributes->get('name')) name="{{ $attributes->get('name') }}{{ is_array($config['value']) ? '[]' : '' }}" @endif
                    {{ $attributes->only('form') }} value="{{ $number }}" x-bind:value="positions[{{ $index }}]" @disabled($disabled)>
            @endforeach
        @endif
    </div>
