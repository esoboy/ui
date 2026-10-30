@blaze

@use('Esoboy\Ui\Support\Options')
@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- options: [['value' => ..., 'label' => ..., 'children' => [...], 'disabled' => bool], ...]. The value is the path of values. --}}
@props([
    'options' => [],
    'placeholder' => null,
    'separator' => '/',
    'changeOnSelect' => false,
    'size' => 'default',
    'disabled' => false,
    'defaultValue' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = array_values(array_map('strval', (array) ($defaultValue ?? Model::value($attributes) ?? [])));
$options = Options::normalize($options);
$labels = Options::labels($options, $value);
@endphp

{{-- <eso-cascader> exposes the chosen path as .value, so wire:model binds on it. --}}
<eso-cascader {{ $attributes->except(['name', 'class', 'style'])->merge([
    'data-slot' => 'cascader',
    'data-value' => json_encode($labels ? $value : []),
    'data-separator' => $separator,
    'data-placeholder' => $placeholder ?? false,
    'data-change-on-select' => (bool) $changeOnSelect,
    'data-name' => $attributes->get('name') ?? false,
]) }}>
    <script type="application/json" data-cascader-options>@json($options)</script>
    {{-- A column and an option, cloned as the path opens; a parent option shows the chevron instead of the check. --}}
    <template data-template="column"><div data-slot="cascader-column" role="listbox"></div></template>
    <template data-template="option"><x-ui::select.item value="" data-cascader-item /></template>
    <template data-template="icon-parent"><x-ui::icon name="chevron-right" /></template>
    <button {{ $attributes->only(['class', 'style']) }} type="button" data-slot="select-trigger" data-cascader-trigger data-size="{{ $size }}" aria-haspopup="listbox" aria-expanded="false" command="toggle-popover" commandfor="" {{ new \Illuminate\View\ComponentAttributeBag($state->attributes()) }} @if (! $labels) data-placeholder @endif @if ($disabled) disabled @endif>
        <span data-slot="select-value">{{ $labels ? implode(" {$separator} ", $labels) : $placeholder }}</span>
        <x-ui::icon name="chevron-down" data-slot="select-icon" />
    </button>
    <div popover="auto" data-slot="cascader-content" data-side="bottom" data-align="start" style="--side-offset: 4px;" wire:ignore></div>
    @if ($attributes->has('name'))
        @foreach ($labels ? $value : [] as $part)
        <input type="hidden" name="{{ $attributes->get('name') }}[]" value="{{ $part }}">
        @endforeach
    @endif
</eso-cascader>
<x-ui::field.error :state="$state" />
<x-ui::extras />
