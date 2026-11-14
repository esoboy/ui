@blaze

@props([
    'showTrigger' => true,
    'showClear' => false,
])

{{-- The input in an Input Group with the trigger and clear button after it, as shadcn's; showTrigger off for the search field inside the content of a Combobox Trigger. --}}
<x-ui::input-group data-combobox-anchor :attributes="$attributes->only(['class', 'style'])">
    <x-ui::input-group.input :attributes="$attributes->except(['class', 'style'])->merge([
        'data-combobox-input' => true,
        'role' => 'combobox',
        'autocomplete' => 'off',
        'aria-autocomplete' => 'list',
        'aria-expanded' => 'false',
    ])" />
    <x-ui::input-group.addon align="inline-end">
        @if ($showTrigger)
            <x-ui::combobox.trigger variant="ghost" size="icon-xs" tabindex="-1" :aria-label="__('ui::combobox.trigger')" :disabled="(bool) $attributes->get('disabled', false)" />
        @endif
        @if ($showClear)
            <x-ui::combobox.clear :disabled="(bool) $attributes->get('disabled', false)" />
        @endif
    </x-ui::input-group.addon>
</x-ui::input-group>
