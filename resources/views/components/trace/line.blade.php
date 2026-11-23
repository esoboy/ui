@blaze
@props(['status' => null])
<span {{ $attributes->merge(['class' => 'ui-trace-line', 'data-status' => $status]) }}>{{ $slot }}</span>
