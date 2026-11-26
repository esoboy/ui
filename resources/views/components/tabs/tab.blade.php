@php($selected = $value === $tabsScope->selection())
<button type="button" role="tab" {{ $attributes->merge(['class' => 'ui-tabs-tab', 'data-value' => $value, 'id' => $tabsScope->idFor('tab', $value)]) }}
    aria-controls="{{ $tabsScope->idFor('panel', $value) }}" aria-selected="{{ $selected ? 'true' : 'false' }}" tabindex="{{ $selected ? 0 : -1 }}"
    wire:bind:aria-selected="active === @js($value) ? 'true' : 'false'"
    x-bind:tabindex="(focused ?? active) === @js($value) ? 0 : -1"
    x-on:focus="focused = @js($value)" x-on:blur="focused = null"
    data-ui-edit="{{ $tabsScope->fieldId() }}" data-ui-next="{{ json_encode($value, JSON_THROW_ON_ERROR) }}">{{ $slot }}</button>
