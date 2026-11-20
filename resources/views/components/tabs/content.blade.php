@props(['value'])
@php($value = (string) $value)
<div role="tabpanel" {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'tabs-content', 'tabindex' => 0]) }}
    x-bind:id="$id('ui-tabs') + '-panel-' + @js($value)"
    x-bind:aria-labelledby="$id('ui-tabs') + '-tab-' + @js($value)"
    x-show="active === @js($value)">{{ $slot }}</div>
