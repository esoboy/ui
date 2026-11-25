@php($previousGroup = null)
@foreach ($choices as $option)
    @if (($showGroupLabels ?? true) && isset($option['group']) && $option['group'] !== $previousGroup)
        <div class="ui-option-group-label" role="presentation">{{ $option['group'] }}</div>
    @endif
    @php($previousGroup = $option['group'] ?? null)
    <button class="ui-{{ $optionSlot ?? 'select-option' }}" type="button" role="option" tabindex="-1"
        id="{{ $this->getId() }}-option-{{ md5(serialize($option['value'])) }}"
        @if (($clientSelect ?? false) && !$multiple)
            @if ($mode === 'controlled') wire:click="$dispatch('value-changed', { value: @js($option['value']) })"
            @else wire:click="$set('value', @js($option['value']))" @endif
        @else wire:click="choose(@js($option['value']))" @endif
        @disabled($disabled || $readonly || $option['disabled'])
        @if (($optionSlot ?? '') === 'command-item') x-on:pointerenter="if ($event.pointerType === 'mouse' && !$el.disabled) highlight($el)" @endif
        @if ($retainInputFocus ?? false) x-on:mousedown.prevent @endif
        aria-selected="{{ $this->isSelected($option['value']) ? 'true' : 'false' }}"
        @if (!$multiple && (($popup ?? false) || ($clientSelect ?? false))) x-on:click="@if($popup ?? false) choosing = true; @endif close(true)" @endif wire:key="option-{{ get_debug_type($option['value']).'-'.$option['value'] }}">
        @if (($optionSlot ?? 'select-option') === 'select-option')
            <span class="ui-option-text">@include('ui::livewire.partials.option-label', ['option' => $option])</span>
        @else
            @include('ui::livewire.partials.option-label', ['option' => $option])
        @endif
        @isset($option['shortcut'])<span class="ui-option-shortcut">{{ $option['shortcut'] }}</span>@endisset
        @if ($this->isSelected($option['value']) && ($optionSlot ?? '') !== 'command-item')
            <span class="ui-option-indicator"><x-ui::icon name="check" /></span>
        @endif
        @if (($optionSlot ?? '') === 'command-item' && empty($option['shortcut']))
            <x-ui::icon name="check" class="ui-command-check" />
        @endif
    </button>
    @if ($option['separatorAfter'] ?? false)
        <div class="ui-option-separator" role="separator"></div>
    @endif
@endforeach
