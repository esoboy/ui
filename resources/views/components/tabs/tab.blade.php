@props(['value'])
@php($value = (string) $value)
<button type="button" role="tab" {{ $attributes->merge(['class' => 'ui-tabs-tab', 'data-value' => $value]) }}
    x-bind:id="$id('ui-tabs') + '-tab-' + @js($value)"
    x-bind:aria-controls="$id('ui-tabs') + '-panel-' + @js($value)"
    x-bind:aria-selected="active === @js($value)"
    x-bind:tabindex="active === @js($value) ? 0 : -1"
    x-on:click="active = @js($value)">{{ $slot }}</button>
