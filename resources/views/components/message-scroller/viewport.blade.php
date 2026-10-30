@blaze

@aware([
    'defaultScrollPosition' => 'end',
])

@props([
    'preserveScrollOnPrepend' => true,
])

{{-- Hidden (data-pending-scroll) until the opening position is applied, so the jump from the top never shows. --}}
<div {{ $attributes->merge([
    'role' => 'region',
    'aria-label' => __('ui::message-scroller.label'),
    'tabindex' => '0',
    'data-slot' => 'message-scroller-viewport',
    'data-preserve-scroll-on-prepend' => $preserveScrollOnPrepend ? false : 'false',
    'data-pending-scroll' => in_array($defaultScrollPosition, ['end', 'last-anchor'], true),
]) }} wire:ignore.self>{{ $slot }}</div>
