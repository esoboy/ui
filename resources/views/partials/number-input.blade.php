@php
    $fieldAttributes = \Esoboy\Ui\Support\ControlAttributes::forInput($attributes->whereDoesntStartWith('wire:'), [
        'name', 'required', 'placeholder', 'autocomplete', 'autofocus', 'inputmode',
        'enterkeyhint', 'list', 'form', 'tabindex', 'invalid',
    ]);
    $model = $attributes->whereStartsWith('wire:model');
    if ($directive = array_key_first($model->all())) {
        $model = new \Illuminate\View\ComponentAttributeBag([\Esoboy\Ui\Support\NativeControl::numericDirective($directive) => $model->first()]);
    }
    $inputEvents = $mode === 'controlled'
        ? ['wire:input' => '$js.proposeNumber($event.target)', 'wire:bind:value' => "externalValue ?? ''"]
        : [(\Livewire\Livewire::current() ? 'wire:input' : 'x-on:input') => "\$dispatch('value-changed', { value: \$event.target.value === '' ? null : \$event.target.valueAsNumber })"];
@endphp
<div x-data="uiNumber" @if($controlKey) wire:key="{{ $controlKey }}" @endif
    data-mode="{{ $mode }}"
    @if ($mode === 'controlled')
        {{ \Esoboy\Ui\Support\ControlAttributes::formReset($attributes) }}
    @else
        x-on:input.capture="checkInput($event)"
    @endif
    x-on:change.capture="commit()" x-on:keydown.enter.capture="commit()"
    x-effect="@if($mode === 'bound') $wire.$get(@js($model->first())); @elseif($mode === 'controlled') $wire.externalValue; @endif $nextTick(() => measure())"
    {{ $attributes->whereDoesntStartWith('wire:')->except(['class', 'wire:key', 'id', ...array_keys($fieldAttributes->all())])->class('ui-number-input-body') }}>
    <x-ui::input-group :size="$size">
        <x-ui::input-group.addon>
        <x-ui::input-group.button size="icon-xs" x-on:click="step(-1)" x-bind:disabled="!decrease" :disabled="$disabled || $readonly || ($min !== null && $numberValue !== null && $numberValue <= $min)"
            :aria-label="__('ui::ui.decrease')"><x-ui::icon name="minus" /></x-ui::input-group.button>
        </x-ui::input-group.addon>
        <x-ui::input-group.input type="number" x-ref="number" :value="$numberValue" :id="$id" :min="$min"
            :max="$max" :step="$step" :disabled="$disabled" :readonly="$readonly"
            :attributes="$fieldAttributes->merge([...$model->all(), ...$attributes->whereStartsWith(['wire:blur', 'wire:focus', 'wire:change'])->all(), ...$inputEvents])" />
        <x-ui::input-group.addon align="inline-end">
        <x-ui::input-group.button size="icon-xs" x-on:click="step(1)" x-bind:disabled="!increase" :disabled="$disabled || $readonly || ($max !== null && $numberValue !== null && $numberValue >= $max)" :aria-label="__('ui::ui.increase')"><x-ui::icon
                name="plus" /></x-ui::input-group.button>
        </x-ui::input-group.addon>
    </x-ui::input-group>
</div>
