@blaze

@aware([
    'open' => false,
])

<div data-slot="trace-collapsible-content" data-collapsible-content x-bind:hidden="! open" @if (! $open) hidden @endif>
    <div {{ $attributes->merge(['data-slot' => 'trace-collapsible-content-inner']) }}>{{ $slot }}</div>
</div>
