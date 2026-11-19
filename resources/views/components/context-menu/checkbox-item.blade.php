@props(['checked' => false, 'disabled' => false, 'closeOnSelect' => true, 'inset' => false])
<x-ui::dropdown-menu.checkbox-item :checked="$checked" :disabled="$disabled" :close-on-select="$closeOnSelect"
    :inset="$inset" data-slot="context-menu-checkbox-item" {{ $attributes }}>{{ $slot }}</x-ui::dropdown-menu.checkbox-item>
