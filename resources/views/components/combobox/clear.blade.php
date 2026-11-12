@blaze

{{-- Clears the choice; hidden while there is none. --}}
<x-ui::button :attributes="$attributes->merge(['variant' => 'ghost', 'size' => 'icon-xs', 'data-combobox-clear' => true, 'aria-label' => __('ui::combobox.clear')])"><x-ui::icon name="x" /></x-ui::button>
