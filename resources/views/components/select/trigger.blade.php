@blaze(fold: true)

@props([
    'size' => 'default',
])

<button {{ $attributes->merge([
    'type' => 'button',
    'data-slot' => 'select-trigger',
    'data-size' => $size,
    'aria-haspopup' => 'listbox',
    'aria-expanded' => 'false',
    'command' => 'toggle-popover',
    'commandfor' => '',
]) }}>
    {{ $slot }}
    <x-ui::icon name="chevron-down" data-slot="select-icon" />
</button>
