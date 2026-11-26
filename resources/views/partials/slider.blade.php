@php
    $disabled = $config['disabled'];
    $readonly = $config['readonly'];
    $orientation = $config['orientation'];
    $inverted = $config['inverted'];
    $fieldAttributes = \Esoboy\Ui\Support\ControlAttributes::forInput($attributes, ['form', 'list', 'tabindex']);
    $thumb = $fieldAttributes->merge(['min' => $config['min'], 'max' => $config['max'], 'step' => $config['step'], 'aria-orientation' => $orientation]);
    $values = is_array($config['value']) ? $config['value'] : [$config['value'] ?? $config['min']];
    sort($values);
    $model = $attributes->whereStartsWith('wire:model');
    $directive = array_key_first($model->all()) ?? ($config['mode'] === 'internal' ? 'wire:model' : null);
    $modelPath = $config['model'];
    if ($directive) $directive = \Esoboy\Ui\Support\NativeControl::numericDirective($directive);
    $name = $attributes->get('name');
    $fieldName = $name === null ? null : $name.(is_array($config['value']) ? '[]' : '');
@endphp
    <div x-data="uiSlider()"
        x-effect="@if($modelPath) $wire.$get(@js($modelPath)); @endif $nextTick(() => refresh(@js($config)))" data-ui-control="{{ json_encode($config, JSON_THROW_ON_ERROR) }}"
        @if($controlKey) wire:key="{{ $controlKey }}" @endif
        data-orientation="{{ $orientation }}" data-inverted="{{ $inverted ? 'true' : 'false' }}"
        x-on:pointerdown="start($event)" x-on:pointermove="move($event)" x-on:pointerup="finish($event)" x-on:pointercancel="finish($event)"
        {{ $attributes->whereDoesntStartWith('wire:model')->except(['class', 'wire:key', 'id', 'name', ...array_keys($fieldAttributes->all())])->class('ui-slider-body') }}>
        @if ($directive)
            <ui-model-field hidden data-slider-model data-initial="{{ json_encode($config['value'], JSON_THROW_ON_ERROR) }}" {{ $directive }}="{{ $modelPath }}"></ui-model-field>
        @endif
        <div class="ui-slider-track">
            <div class="ui-slider-range" x-bind:style="{
                '--ui-slider-position': positions.length > 1 ? offset(0, true) : '0%',
                '--ui-slider-end': offset(positions.length - 1, true)
            }"></div>
        </div>
        @foreach ($values as $index => $number)
            <input wire:key="thumb-{{ $index }}" class="ui-slider-thumb" type="range" data-slider-index="{{ $index }}"
                @if ($fieldName && ! $readonly) data-field-name="{{ $fieldName }}" @if ($config['value'] !== null) name="{{ $fieldName }}" @endif @endif
                value="{{ $number }}" {{ $thumb->merge(['id' => ($index && $inputId ? $inputId.'-'.$index : $inputId), 'aria-label' => __('ui::ui.slider_value', ['number' => $index + 1])]) }}
                x-bind:style="{ '--ui-slider-position': offset({{ $index }}) }" x-on:input="readThumb({{ $index }})"
                x-on:change="commitThumb('change')" x-on:blur="commitThumb('blur')" x-on:keydown.enter="commitThumb('keydown')" x-on:keydown="key($event, {{ $index }})" @disabled($disabled || $readonly)>
        @endforeach
        @if ($readonly && $fieldName && $config['value'] !== null)
            @foreach ($values as $index => $number)
                <input wire:key="form-thumb-{{ $index }}" type="hidden" name="{{ $fieldName }}"
                    {{ $attributes->only('form') }} value="{{ $number }}" x-bind:value="positions[{{ $index }}]" @disabled($disabled)>
            @endforeach
        @endif
    </div>
