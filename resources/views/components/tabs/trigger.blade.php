@blaze

@aware([
    'uiValue' => null,
    'uiModel' => null,
])

@props([
    'value',
])

@php
$selected = \Esoboy\Ui\Support\Model::inherited($uiModel, $uiValue);
$active = $selected !== null ? (string) $selected === (string) $value : \Esoboy\Ui\Support\Sequence::next('tabs-trigger') === 1;
@endphp

{{-- wire:key: its script gives it an id, and Livewire's morph would otherwise match it by that id and replace it. --}}
<button {{ $attributes->merge([
    'type' => 'button',
    'role' => 'tab',
    'wire:key' => "tabs-trigger-{$value}",
    'data-slot' => 'tabs-trigger',
    'data-value' => $value,
    'data-active' => $active,
    'aria-selected' => $active ? 'true' : 'false',
    'tabindex' => $active ? '0' : '-1',
    'x-bind:data-active' => 'value === '.json_encode((string) $value),
    'x-bind:aria-selected' => 'String(value === '.json_encode((string) $value).')',
    'x-bind:tabindex' => 'value === '.json_encode((string) $value).' ? 0 : -1',
]) }}>{{ $slot }}</button>
