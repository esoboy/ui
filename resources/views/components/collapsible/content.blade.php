@blaze

@aware([
    'uiOpen' => false,
    'uiModel' => null,
])

@php($open = (bool) \Esoboy\Ui\Support\Model::inherited($uiModel, $uiOpen))

{{-- wire:key: its script gives it an id, and Livewire's morph would otherwise match it by that id and replace it. --}}
<div {{ $attributes->merge(['data-slot' => 'collapsible-content', 'wire:key' => 'collapsible-content', 'data-open' => (bool) $open, 'hidden' => ! $open, 'x-bind:hidden' => '! open', 'x-bind:data-open' => 'open']) }}>{{ $slot }}</div>
