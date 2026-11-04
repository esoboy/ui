@blaze

@props([
    'variant' => 'default',
])

{{-- An empty line holds a newline so copying a selection keeps blank lines. --}}
<span {{ $attributes->merge(['data-slot' => 'code-block-line', 'data-variant' => $variant]) }}><span data-slot="code-block-line-content">@if ($slot->isEmpty()){{ "\n" }}@else{{ $slot }}@endif</span></span>
