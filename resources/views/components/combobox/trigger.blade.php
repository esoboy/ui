@blaze(fold: true)

{{-- Opens the list. Combobox Input shows one as its chevron; on its own it is a Button showing Combobox Value, with the input inside the content (shadcn's combobox with a trigger). --}}
<x-ui::button :attributes="$attributes->merge(['data-combobox-trigger' => true, 'aria-haspopup' => 'listbox', 'aria-expanded' => 'false'])">{{ $slot }}<x-ui::icon name="chevron-down" data-slot="combobox-trigger-icon" /></x-ui::button>
