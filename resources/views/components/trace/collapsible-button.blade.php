@blaze
<button {{ $attributes->merge(['type' => 'button', 'class' => 'ui-trace-collapsible-button']) }}>
    {{ $slot }}
    <x-ui::trace.icon><x-ui::icon name="chevron-right" /></x-ui::trace.icon>
</button>
