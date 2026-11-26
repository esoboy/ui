@blaze
@props(['src', 'alt' => ''])
<img src="{{ $src }}" alt="{{ $alt }}"
    x-data x-init="if ($el.complete && $el.currentSrc) $el.toggleAttribute($el.naturalWidth ? 'data-loaded' : 'data-failed', true)"
    x-on:load="$el.removeAttribute('data-failed'); $el.setAttribute('data-loaded', '')"
    x-on:error="$el.removeAttribute('data-loaded'); $el.setAttribute('data-failed', '')"
    {{ $attributes->merge(['class' => 'ui-avatar-image', 'loading' => 'lazy', 'decoding' => 'async']) }}>
