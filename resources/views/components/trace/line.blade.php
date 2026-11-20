@blaze
@props(['status' => null])
<span {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'trace-line', 'data-status' => $status]) }}>{{ $slot }}</span>
