@blaze
@props(['src', 'alt' => ''])
<img src="{{ $src }}" alt="{{ $alt }}"
    x-init="loaded = $el.complete && $el.naturalWidth > 0" x-on:load="loaded = true" x-on:error="loaded = false" x-bind:class="{ 'ui-avatar-pending': !loaded }"
    {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'avatar-image', 'loading' => 'lazy', 'decoding' => 'async']) }}>
