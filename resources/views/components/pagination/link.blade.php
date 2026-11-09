@blaze

@props([
    'active' => false,
    'size' => 'icon',
])

{{-- A Button: an <a> with href, otherwise a <button> for wire:click. --}}
<x-ui::button :variant="$active ? 'outline' : 'ghost'" :size="$size" :attributes="$attributes->merge(['aria-current' => $active ? 'page' : false, 'data-active' => $active])">{{ $slot }}</x-ui::button>
