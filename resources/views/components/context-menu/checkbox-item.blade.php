@props(['checked' => false, 'disabled' => false, 'closeOnSelect' => true, 'inset' => false])
<x-ui::dropdown-menu.checkbox-item :checked="$checked" :disabled="$disabled" :close-on-select="$closeOnSelect"
    :inset="$inset" {{ $attributes->class('ui-context-menu-checkbox-item') }}>{{ $slot }}</x-ui::dropdown-menu.checkbox-item>
