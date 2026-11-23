@props(['open' => false, 'disabled' => false])
@aware(['multiple' => false])
<details {{ $attributes->merge(['class' => 'ui-accordion-item', 'data-disabled' => $disabled ? 'true' : null]) }} @if(!$multiple) x-bind:name="$id('ui-accordion')" @endif @if ($open) open @endif>
    {{ $slot }}
</details>
