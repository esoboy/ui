@blaze
<button {{ $attributes->merge(['type' => 'button', 'data-esoboy-ui' => '', 'data-slot' => 'trace-collapsible-button']) }}>
    {{ $slot }}
    <x-ui::trace.icon><x-ui::icon name="chevron-right" /></x-ui::trace.icon>
</button>
