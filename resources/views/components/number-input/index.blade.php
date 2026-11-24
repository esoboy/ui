@props(['size' => 'default', 'value', 'initialValue', 'min' => null, 'max' => null, 'step' => 1])
@php
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'number-input', get_defined_vars(), ['min' => $min, 'max' => $max, 'step' => $step]);
    $disabled = $binding->config['disabled'];
    $readonly = $binding->config['readonly'];
    $fieldAttributes = \Esoboy\Ui\Support\ControlAttributes::forInput($attributes, [
        'name', 'required', 'placeholder', 'autocomplete', 'autofocus', 'inputmode',
        'enterkeyhint', 'list', 'form', 'tabindex', 'invalid',
    ]);
@endphp
<div {{ $attributes->only('class')->class('ui-number-input ui-number-input-field') }}>
    <div x-data="uiNumber()"
        data-ui-control="{{ json_encode($binding->config, JSON_THROW_ON_ERROR) }}"
        @if($binding->key) wire:key="{{ $binding->key }}" @endif
        x-on:change="changed($event)" x-on:focusout="blurred($event)" x-on:keydown.enter="entered($event)"
        {{ $attributes->except(['class', 'wire:key', 'id', ...array_keys($fieldAttributes->all())])->class('ui-number-input-body') }}>
        <x-ui::input-group :size="$size">
        <x-ui::input-group.addon>
        <x-ui::input-group.button size="icon-xs" x-on:click="adjust(-1)" x-bind:disabled="config.disabled || config.readonly || (config.min !== null && value !== null && value <= config.min)" :disabled="$disabled || $readonly || ($min !== null && $binding->config['value'] !== null && $binding->config['value'] <= $min)"
            :aria-label="__('ui::ui.decrease')"><x-ui::icon name="minus" /></x-ui::input-group.button>
        </x-ui::input-group.addon>
        <x-ui::input-group.input type="number" x-ref="number" x-on:input.stop="input($event)" x-on:change="commit()" x-on:keydown.enter="commit()" :value="$binding->config['value']" :id="$binding->id" :min="$min"
            :max="$max" :step="$step" :disabled="$disabled" :readonly="$readonly"
            :attributes="$fieldAttributes" />
        <x-ui::input-group.addon align="inline-end">
        <x-ui::input-group.button size="icon-xs" x-on:click="adjust(1)" x-bind:disabled="config.disabled || config.readonly || (config.max !== null && value !== null && value >= config.max)" :disabled="$disabled || $readonly || ($max !== null && $binding->config['value'] !== null && $binding->config['value'] >= $max)" :aria-label="__('ui::ui.increase')"><x-ui::icon
                name="plus" /></x-ui::input-group.button>
        </x-ui::input-group.addon>
        </x-ui::input-group>
    </div>
    {{ $slot }}
    @isset($footer)
        <div class="ui-number-input-footer">{{ $footer }}</div>
    @endisset
</div>
