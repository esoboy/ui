@props(['variant' => 'ghost', 'size' => 'icon-xs'])
<x-ui::button :variant="$variant" :size="$size" data-slot="attachment-action" {{ $attributes }}>{{ $slot }}</x-ui::button>
