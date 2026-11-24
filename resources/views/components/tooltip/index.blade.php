@props(['openDelay' => 600, 'closeDelay' => 0, 'disabled' => false])
@includeIsolated('ui::partials.hover-popup', [
    'attributes' => $attributes->merge(['class' => 'ui-tooltip', 'data-disabled' => $disabled ? 'true' : 'false']),
    'tooltip' => true, 'openDelay' => $openDelay, 'closeDelay' => $closeDelay, 'slot' => $slot,
])
