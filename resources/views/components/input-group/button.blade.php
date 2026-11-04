@blaze

@props([
    'variant' => 'ghost',
    'size' => 'xs',
])

<x-ui::button :variant="$variant" :size="$size" :attributes="$attributes">{{ $slot }}</x-ui::button>
