@blaze

{{-- The class goes to the wrapper, everything else to the input, as in shadcn. --}}
<div {{ $attributes->only('class')->merge(['data-slot' => 'emoji-picker-search-wrapper']) }} wire:ignore>
    <x-ui::input-group class="h-9 rounded-none border-0 bg-transparent shadow-none ring-0">
        <x-ui::input-group.input :attributes="$attributes->except('class')->merge([
            'data-emoji-picker-search' => true,
            'placeholder' => __('ui::emoji-picker.search'),
            'aria-label' => __('ui::emoji-picker.search'),
            'autocomplete' => 'off',
            'spellcheck' => 'false',
            'class' => 'text-sm',
        ])" />
        <x-ui::input-group.addon class="pl-0"><x-ui::icon name="search" class="opacity-50" /></x-ui::input-group.addon>
    </x-ui::input-group>
</div>
