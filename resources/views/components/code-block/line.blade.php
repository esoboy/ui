@blaze

@props([
    'variant' => 'default',
    'tokens' => null,
])

{{-- An empty line holds a newline so copying a selection keeps blank lines. --}}
<span {{ $attributes->merge(['data-slot' => 'code-block-line', 'data-variant' => $variant]) }}><span data-slot="code-block-line-content">@if ($tokens)@include('ui::partials.code-tokens')@elseif ($slot->isEmpty()){{ "\n" }}@else{{ $slot }}@endif</span></span>
