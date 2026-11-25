@props(['value'])
@aware(['tabsScope'])
@php
    $value = (string) $value;
    $tabsScope->register($value, (bool) $attributes->get('disabled'));
    $selected = $value === $tabsScope->selection();
@endphp
<button type="button" role="tab" {{ $attributes->merge(['class' => 'ui-tabs-tab', 'data-value' => $value, 'id' => $tabsScope->groupId.'-tab-'.$value]) }}
    aria-controls="{{ $tabsScope->groupId }}-panel-{{ $value }}" aria-selected="{{ $selected ? 'true' : 'false' }}" tabindex="{{ $selected ? 0 : -1 }}"
    x-bind:aria-selected="active === @js($value)"
    x-bind:tabindex="(focused ?? active) === @js($value) ? 0 : -1"
    x-on:focus="focused = @js($value)" x-on:blur="focused = null"
    x-on:click="active = @js($value)">{{ $slot }}</button>
