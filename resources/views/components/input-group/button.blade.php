@props(['variant' => 'ghost', 'size' => 'xs', 'type' => 'button'])
<x-ui::button :variant="$variant" :type="$type" :data-size="$size" :attributes="$attributes->class('ui-input-group-button')">{{ $slot }}</x-ui::button>
