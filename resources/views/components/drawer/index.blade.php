@props(['initialOpen' => false, 'showSwipeHandle' => false, 'modal' => true, 'snapPoints' => [], 'initialSnapPoint' => null, 'snapToSequentialPoints' => false])
@php
    foreach ($snapPoints as $point) {
        if (! ((is_int($point) || is_float($point)) && is_finite((float) $point) && $point > 0) && ! (is_string($point) && preg_match('/^\d+(?:\.\d+)?(?:px|rem)$/', $point))) throw new \InvalidArgumentException(__('ui::errors.drawer_points'));
    }
    if ($initialSnapPoint !== null && !in_array($initialSnapPoint, $snapPoints, true)) throw new \InvalidArgumentException(__('ui::errors.drawer_initial_point'));
@endphp
<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'drawer', 'data-swipe-handle' => $showSwipeHandle ? 'true' : 'false']) }} data-ui-overlay x-data="uiDrawer(@js($initialOpen), @js($modal), @js($snapPoints), @js($initialSnapPoint), @js($snapToSequentialPoints))" x-modelable="opened" x-id="['ui-dialog']">
    {{ $slot }}</div>
