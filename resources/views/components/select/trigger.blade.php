@blaze(fold: true)

@props([
    'size' => 'default',
])

{{-- Opens the Select Content natively; commandfor stays empty, <eso-select> points it there. --}}
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
