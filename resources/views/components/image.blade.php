@blaze
@props(['src', 'alt'])
<img src="{{ $src }}" alt="{{ $alt }}"
    {{ $attributes->merge(['loading' => 'lazy', 'decoding' => 'async', 'class' => 'ui-image']) }}>
