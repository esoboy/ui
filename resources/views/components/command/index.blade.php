@blaze

@props([
    'shouldFilter' => true,
])

{{-- <eso-command> filters the items as you type and runs the highlighted one on Enter. --}}
<eso-command {{ $attributes->merge(['data-slot' => 'command', 'data-should-filter' => $shouldFilter ? 'true' : 'false']) }}>{{ $slot }}</eso-command>
