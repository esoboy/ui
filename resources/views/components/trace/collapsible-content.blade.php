@blaze

@aware([
    'defaultOpen' => false,
])

<div data-slot="trace-collapsible-content" data-collapsible-content @if (! $defaultOpen) hidden @endif>
    <div {{ $attributes->merge(['data-slot' => 'trace-collapsible-content-inner']) }}>{{ $slot }}</div>
</div>
