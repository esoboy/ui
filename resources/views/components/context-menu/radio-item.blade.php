@props(['checked' => false, 'disabled' => false, 'closeOnSelect' => true, 'inset' => false])
<x-ui::dropdown-menu.radio-item :checked="$checked" :disabled="$disabled" :close-on-select="$closeOnSelect"
    :inset="$inset" data-slot="context-menu-radio-item" {{ $attributes }}>{{ $slot }}</x-ui::dropdown-menu.radio-item>
