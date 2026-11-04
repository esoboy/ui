@blaze

{{-- A Collapsible: default-open and wire:model work as on <x-ui::collapsible>. --}}
<x-ui::collapsible :attributes="$attributes->merge(['data-slot' => 'trace-collapsible'])">{{ $slot }}</x-ui::collapsible>
