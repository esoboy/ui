@props(['active' => false, 'size' => 'icon', 'link' => null])
<x-ui::button :variant="$active ? 'outline' : 'ghost'" :size="$size" :link="$link" data-slot="pagination-button"
    :data-active="$active ? 'true' : 'false'" :aria-current="$active ? 'page' : null" :attributes="$attributes">{{ $slot }}</x-ui::button>
