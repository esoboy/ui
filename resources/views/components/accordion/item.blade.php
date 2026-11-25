@aware(['accordion' => null])
<details {{ $attributes->merge(['class' => 'ui-accordion-item', 'data-disabled' => $disabled ? 'true' : null]) }} @if($accordion && !$accordion->multiple) name="{{ $accordion->groupName }}" @endif @if ($open) open @endif>
    {{ $slot }}
</details>
