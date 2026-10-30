@blaze

{{-- The class goes to the wrapper, everything else to the input, as in shadcn. --}}
<div {{ $attributes->only('class')->merge(['data-slot' => 'emoji-picker-search-wrapper']) }} wire:ignore>
    <x-ui::icon name="search" />
    <input {{ $attributes->except('class')->merge([
        'type' => 'text',
        'data-slot' => 'emoji-picker-search',
        'placeholder' => __('ui::emoji-picker.search'),
        'aria-label' => __('ui::emoji-picker.search'),
        'autocomplete' => 'off',
        'spellcheck' => 'false',
    ]) }}>
</div>
