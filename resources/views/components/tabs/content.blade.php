@props(['value'])
@aware(['tabsScope'])
@php($value = (string) $value)
<div role="tabpanel" {{ $attributes->merge(['class' => 'ui-tabs-content', 'tabindex' => 0, 'id' => $tabsScope->groupId.'-panel-'.$value, 'data-value' => $value]) }}
    aria-labelledby="{{ $tabsScope->groupId }}-tab-{{ $value }}" @if($tabsScope->selection() !== $value) hidden @endif
    x-bind:hidden="active !== @js($value)"
>{{ $slot }}</div>
