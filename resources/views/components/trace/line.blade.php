@blaze(fold: true)

@props([
    'status' => null,
])

{{-- status: running (the title shimmers), error or pending. --}}
<span {{ $attributes->merge(['data-slot' => 'trace-line', 'data-status' => $status ?? false]) }}>{{ $slot }}</span>
