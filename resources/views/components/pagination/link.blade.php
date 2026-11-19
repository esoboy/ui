@props(['active' => false, 'size' => 'icon'])
<x-ui::button :link="$attributes->get('href', '#')" :variant="$active ? 'outline' : 'ghost'" :size="$size"
    data-slot="pagination-link" :data-active="$active ? 'true' : 'false'" :aria-current="$active ? 'page' : null" :attributes="$attributes">{{ $slot }}</x-ui::button>
