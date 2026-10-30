@blaze

@props([
    'value',
])

<div {{ $attributes->merge([
    'role' => 'option',
    'tabindex' => '-1',
    'data-slot' => 'select-item',
    'data-value' => $value,
    'aria-selected' => 'false',
    'aria-disabled' => $attributes->has('disabled') ? 'true' : false,
]) }}>
    <span data-slot="select-item-text">{{ $slot }}</span>
    <span data-slot="select-item-indicator"><x-ui::icon name="check" /></span>
</div>
