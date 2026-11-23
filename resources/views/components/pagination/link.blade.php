@props(['active' => false, 'size' => 'icon'])
<x-ui::button :link="$attributes->get('href', '#')" :variant="$active ? 'outline' : 'ghost'" :size="$size"
    :data-active="$active ? 'true' : 'false'" :aria-current="$active ? 'page' : null" :attributes="$attributes->class('ui-pagination-link')">{{ $slot }}</x-ui::button>
