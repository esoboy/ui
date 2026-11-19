@props(['position' => 'bottom-right', 'limit' => 3])
<section x-data="uiToaster(@js($limit))" x-on:ui-toast-layout="closing($event)" x-on:keydown.window="shortcut($event)" x-on:keydown="navigate($event)" x-on:blur.window="windowFocus(false, $event)" x-on:focus.window="windowFocus(true, $event)" x-on:ui-toast-dismissed="schedule()" x-on:mouseenter="hover(true)" x-on:mouseleave="hover(false)" x-on:focusin="focus($event)" x-on:focusout="focus($event)"
    {{ $attributes->merge(['data-slot' => 'toaster', 'tabindex' => -1, 'role' => 'region', 'aria-live' => 'polite', 'aria-atomic' => 'false', 'aria-relevant' => 'additions text', 'data-position' => $position, 'aria-label' => __('ui::ui.notifications')]) }}>
    {{ $slot }}</section>
