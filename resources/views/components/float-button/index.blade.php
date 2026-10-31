@blaze

@props([
    'variant' => 'outline',
    'size' => 'icon-lg',
])

{{-- A Button for Float Button Group, which makes it round and raised. --}}
<x-ui::button :variant="$variant" :size="$size" :attributes="$attributes">{{ $slot }}</x-ui::button>
