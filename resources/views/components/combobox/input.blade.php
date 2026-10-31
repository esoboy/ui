@blaze

@props([
    'showTrigger' => true,
    'showClear' => false,
])

{{-- The input in an Input Group with the trigger and clear button after it, as shadcn's; showTrigger off for the search field inside the content of a Combobox Trigger. --}}
<div {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'input-group', 'role' => 'group', 'data-combobox-anchor' => true]) }}>
    <input {{ $attributes->except(['class', 'style'])->merge([
        'type' => 'text',
        'data-slot' => 'input-group-control',
        'data-combobox-input' => true,
        'role' => 'combobox',
        'autocomplete' => 'off',
        'aria-autocomplete' => 'list',
        'aria-expanded' => 'false',
    ]) }}>
    <div data-slot="input-group-addon" data-align="inline-end" role="group">
        @if ($showTrigger)
            <x-ui::combobox.trigger variant="ghost" size="icon-xs" tabindex="-1" :aria-label="__('ui::combobox.trigger')" :disabled="$attributes->has('disabled')" />
        @endif
        @if ($showClear)
            <x-ui::combobox.clear :disabled="$attributes->has('disabled')" />
        @endif
    </div>
</div>
