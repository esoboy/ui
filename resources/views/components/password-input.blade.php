@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- shadcn's password Input Group, its eye a show/hide toggle. class styles the group, the rest goes to the input. --}}
@props([
    'disabled' => false,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
@endphp

<eso-password-input {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'password-input']) }}>
    <x-ui::input-group :render-error="false">
        <x-ui::input-group.input type="password" :state="$state" :disabled="$disabled" :attributes="$attributes->except(['class', 'style'])" />
        <x-ui::input-group.addon align="inline-end">
            <x-ui::input-group.button size="icon-xs" data-password-toggle aria-pressed="false" :aria-label="__('ui::password-input.toggle')" :disabled="$disabled">
                <x-ui::icon name="eye-off" data-password-icon="hidden" />
                <x-ui::icon name="eye" data-password-icon="shown" />
            </x-ui::input-group.button>
        </x-ui::input-group.addon>
    </x-ui::input-group>
</eso-password-input>
<x-ui::field.error :state="$state" />
<x-ui::extras />
