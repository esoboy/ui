@blaze
@props(['status' => null])
<span {{ $attributes->merge(['data-slot' => 'trace-line', 'data-status' => $status]) }}>{{ $slot }}</span>
