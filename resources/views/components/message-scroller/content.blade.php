@blaze

<div {{ $attributes->merge(['role' => 'log', 'aria-relevant' => 'additions', 'data-slot' => 'message-scroller-content']) }}>
    {{ $slot }}
    <div aria-hidden="true" data-message-scroller-spacer hidden wire:ignore></div>
</div>
