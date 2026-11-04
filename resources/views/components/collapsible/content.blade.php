@blaze

@aware([
    'defaultOpen' => false,
])

{{-- wire:key: its script gives it an id, and Livewire's morph would otherwise match it by that id and replace it. --}}
<div {{ $attributes->merge(['data-slot' => 'collapsible-content', 'wire:key' => 'collapsible-content', 'data-open' => (bool) $defaultOpen, 'hidden' => ! $defaultOpen]) }}>{{ $slot }}</div>
