@blaze

@aware([
    'uiValue' => null,
    'uiModel' => null,
])

@php
$selected = \Esoboy\Ui\Support\Model::inherited($uiModel, $uiValue);
$active = $selected !== null ? (string) $selected === (string) $value : \Esoboy\Ui\Support\Sequence::next('tabs-content') === 1;
@endphp

@props([
    'value',
])

{{-- wire:key: its script gives it an id, and Livewire's morph would otherwise match it by that id and replace it. --}}
<div {{ $attributes->merge([
    'role' => 'tabpanel',
    'wire:key' => "tabs-content-{$value}",
    'tabindex' => '0',
    'data-slot' => 'tabs-content',
    'data-value' => $value,
    'hidden' => ! $active,
    'x-bind:hidden' => 'value !== '.json_encode((string) $value),
]) }}>{{ $slot }}</div>
