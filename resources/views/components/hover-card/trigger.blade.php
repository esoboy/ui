@blaze

@aware(['uiHoverId' => null])

@props(['delay' => 600, 'closeDelay' => 300])

{!! \Esoboy\Ui\Support\ControlSlot::overlay((string) $slot, $attributes, 'hover-card', $uiHoverId, ['data-delay' => $delay, 'data-close-delay' => $closeDelay]) !!}
