@props(['variant' => 'ghost', 'size' => 'icon-xs'])
<x-ui::button :variant="$variant" :size="$size" {{ $attributes->class('ui-attachment-action') }}>{{ $slot }}</x-ui::button>
