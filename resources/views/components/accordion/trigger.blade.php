@blaze

@aware([
    'disabled' => false,
])

<summary {{ $attributes->merge(['data-slot' => 'accordion-trigger', 'aria-disabled' => $disabled ? 'true' : false, 'tabindex' => $disabled ? '-1' : false]) }}>
    {{ $slot }}
    <x-ui::icon name="chevron-down" data-slot="accordion-trigger-icon" data-state="closed" />
    <x-ui::icon name="chevron-up" data-slot="accordion-trigger-icon" data-state="open" />
</summary>
