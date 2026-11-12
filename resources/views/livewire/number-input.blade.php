@php
    $min = $settings['min'] ?? null;
    $max = $settings['max'] ?? null;
    $step = $settings['step'] ?? 1;
    $disabled = $settings['disabled'] ?? false;
    $number = is_numeric($value) ? $value + 0 : null;
    $labelling = ['id', 'aria-label', 'aria-labelledby'];
@endphp
<eso-number-input {{ $attributes->whereDoesntStartWith('wire:model')->except(['name', ...$labelling])->merge([
    'data-slot' => 'number-input',
    'data-min' => $min,
    'data-max' => $max,
    'data-step' => $step,
    'x-data' => 'uiNumberInput',
    'x-on:ui-model-changed.self' => 'notifyModel()',
    'x-on:focusout' => '!$el.contains($event.relatedTarget) && blurModel()',
    'x-on:pointerdown' => 'press($event)',
    'x-on:pointerup.window' => 'stop()',
    'x-on:pointercancel.window' => 'stop()',
    'x-on:click.capture' => 'afterRepeat($event)',
]) }}>
    <x-ui::input-group :render-error="false">
        <x-ui::input-group.addon>
            <x-ui::button variant="ghost" size="icon-xs" data-number-input="decrement" :aria-label="__('ui::number-input.decrement')" tabindex="-1" :disabled="$disabled || ($min !== null && $number !== null && $number <= $min)" wire:click="step(-1)">
                <x-ui::icon name="minus" />
            </x-ui::button>
        </x-ui::input-group.addon>
        <x-ui::input-group.input inputmode="decimal" role="spinbutton" wire:model.live.debounce.250ms.number="value" wire:keydown.up.prevent="step(1)" wire:keydown.down.prevent="step(-1)" :value="$number ?? ''" :placeholder="$settings['placeholder'] ?? null" autocomplete="off" :name="$attributes->get('name')" :aria-valuemin="$min" :aria-valuemax="$max" :aria-valuenow="$number" :disabled="$disabled" :attributes="$attributes->only($labelling)->merge($settings['validation'] ?? [])" />
        <x-ui::input-group.addon align="inline-end">
            <x-ui::button variant="ghost" size="icon-xs" data-number-input="increment" :aria-label="__('ui::number-input.increment')" tabindex="-1" :disabled="$disabled || ($max !== null && $number !== null && $number >= $max)" wire:click="step(1)">
                <x-ui::icon name="plus" />
            </x-ui::button>
        </x-ui::input-group.addon>
    </x-ui::input-group>
</eso-number-input>
