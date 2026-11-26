@props(['initialOpen' => false, 'showSwipeHandle' => false, 'modal' => true, 'snapPoints' => [], 'initialSnapPoint' => null, 'snapToSequentialPoints' => false])
@php
    foreach ($snapPoints as $point) {
        if (! ((is_int($point) || is_float($point)) && is_finite((float) $point) && $point > 0) && ! (is_string($point) && preg_match('/^\d+(?:\.\d+)?(?:px|rem)$/', $point))) throw new \InvalidArgumentException(__('ui::errors.drawer_points'));
    }
    if ($initialSnapPoint !== null && !in_array($initialSnapPoint, $snapPoints, true)) throw new \InvalidArgumentException(__('ui::errors.drawer_initial_point'));
@endphp
<div {{ $attributes->whereDoesntStartWith('wire:model')->except('value')->merge(['class' => 'ui-drawer', 'data-swipe-handle' => $showSwipeHandle ? 'true' : 'false']) }} @if ($attributes->whereStartsWith('x-model')->isNotEmpty()) x-modelable="opened" @endif data-ui-overlay x-data="uiDrawer(@js($initialOpen), @js($modal), @js($snapPoints), @js($initialSnapPoint), @js($snapToSequentialPoints))" x-id="['ui-dialog']">
    @includeIsolated('ui::partials.dialog-state', ['kind' => 'drawer', 'attributes' => $attributes, 'initialOpen' => $initialOpen])
    {{ $slot }}</div>
