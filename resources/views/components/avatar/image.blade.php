@blaze
@props(['src', 'alt' => ''])
<img src="{{ $src }}" alt="{{ $alt }}"
    x-data="{ loaded: $el.complete && $el.naturalWidth > 0 }" x-on:load="loaded = true" x-on:error="loaded = false" x-bind:data-loaded="loaded ? '' : null"
    {{ $attributes->merge(['class' => 'ui-avatar-image', 'loading' => 'lazy', 'decoding' => 'async']) }}>
