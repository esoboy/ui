@props(['position' => 'bottom-right', 'limit' => 3])
<section x-data="uiToaster(@js($limit))" x-on:ui-toast-layout="closing($event)" x-on:keydown.f6.window="shortcut($event)" x-on:keydown.tab="navigate($event)"
    x-on:blur.window="if ($event.target === window) expand()" x-on:focus.window="if ($event.target === window) expand()" x-on:ui-toast-dismissed="schedule()"
    x-on:mouseenter="hovering = true; expand()" x-on:mouseleave="hovering = false; expand()"
    x-on:focusin="focused = $event.target.matches(':focus-visible'); expand()" x-on:focusout="leave($event.relatedTarget)"
    x-on:resize.window="schedule()" {{ $attributes->merge(['class' => 'ui-toaster', 'tabindex' => -1, 'role' => 'region', 'aria-live' => 'polite', 'aria-atomic' => 'false', 'aria-relevant' => 'additions text', 'data-position' => $position, 'aria-label' => __('ui::ui.notifications')]) }}>
    {{ $slot }}</section>
