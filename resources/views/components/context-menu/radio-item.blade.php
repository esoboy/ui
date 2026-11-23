@props(['checked' => false, 'disabled' => false, 'closeOnSelect' => true, 'inset' => false])
<x-ui::dropdown-menu.radio-item :checked="$checked" :disabled="$disabled" :close-on-select="$closeOnSelect"
    :inset="$inset" {{ $attributes->class('ui-context-menu-radio-item') }}>{{ $slot }}</x-ui::dropdown-menu.radio-item>
