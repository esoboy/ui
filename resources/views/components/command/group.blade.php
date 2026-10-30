@blaze(fold: true)

@props([
    'heading' => null,
])

<div {{ $attributes->merge(['role' => 'presentation', 'data-slot' => 'command-group']) }}>
    @if ($heading !== null)
        <div data-slot="command-group-heading" aria-hidden="true">{{ $heading }}</div>
    @endif
    <div role="group">{{ $slot }}</div>
</div>
