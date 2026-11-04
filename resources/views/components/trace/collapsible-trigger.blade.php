@blaze

@aware([
    'defaultOpen' => false,
])

<button {{ $attributes->merge(['type' => 'button', 'data-slot' => 'trace-collapsible-trigger', 'data-collapsible-trigger' => true, 'aria-expanded' => $defaultOpen ? 'true' : 'false']) }}>
    {{ $slot }}
    <span data-slot="trace-icon" aria-hidden="true"><x-ui::icon name="chevron-right" /></span>
</button>
