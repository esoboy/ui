@blaze

@aware([
    'defaultOpen' => false,
])

{{-- Any element marked data-collapsible-trigger toggles too, e.g. a Button or a sidebar menu button. --}}
<button {{ $attributes->merge(['type' => 'button', 'data-slot' => 'collapsible-trigger', 'aria-expanded' => $defaultOpen ? 'true' : 'false', 'data-panel-open' => (bool) $defaultOpen]) }}>{{ $slot }}</button>
