@props(['active' => false, 'size' => null])
@aware(['paginationSize' => 'default'])
@php($size ??= \Esoboy\Ui\Support\ComponentSize::icon($paginationSize))
<x-ui::button :link="$attributes->get('href', '#')" :variant="$active ? 'outline' : 'ghost'" :size="$size"
    :data-active="$active ? 'true' : 'false'" :aria-current="$active ? 'page' : null" :attributes="$attributes->class('ui-pagination-link')">{{ $slot }}</x-ui::button>
