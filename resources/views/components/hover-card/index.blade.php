@props(['openDelay' => 600, 'closeDelay' => 300])
@includeIsolated('ui::partials.hover-popup', [
    'attributes' => $attributes->class('ui-hover-card'),
    'tooltip' => false, 'openDelay' => $openDelay, 'closeDelay' => $closeDelay, 'slot' => $slot,
])
