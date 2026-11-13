@blaze

@props([
    'variant' => 'ghost',
    'size' => 'xs',
])

<x-ui::button :variant="$variant" :attributes="$attributes->merge(['data-input-group-size' => $size])">{{ $slot }}</x-ui::button>
