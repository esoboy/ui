@php($previousGroup = null)
@foreach ($choices as $option)
    @if (($showGroupLabels ?? true) && isset($option['group']) && $option['group'] !== $previousGroup)
        <div data-slot="option-group-label" role="presentation">{{ $option['group'] }}</div>
    @endif
    @php($previousGroup = $option['group'] ?? null)
    <button type="button" role="option" tabindex="-1" data-slot="{{ $optionSlot ?? 'select-option' }}"
        id="{{ $this->getId() }}-option-{{ md5(serialize($option['value'])) }}"
        wire:click="choose(@js($option['value']))" @disabled($disabled || $readonly || $option['disabled'])
        @if ($retainInputFocus ?? false) x-on:mousedown.prevent @endif
        aria-selected="{{ $this->isSelected($option['value']) ? 'true' : 'false' }}"
        @if (!$multiple && ($popup ?? false)) x-on:click="close(true)" @endif wire:key="option-{{ get_debug_type($option['value']).'-'.$option['value'] }}">
        @if (($optionSlot ?? 'select-option') === 'select-option')
            <span data-slot="option-text">@include('ui::livewire.partials.option-label', ['option' => $option])</span>
        @else
            @include('ui::livewire.partials.option-label', ['option' => $option])
        @endif
        @isset($option['shortcut'])<span data-slot="option-shortcut">{{ $option['shortcut'] }}</span>@endisset
        @if ($this->isSelected($option['value']) && ($optionSlot ?? '') !== 'command-item')
            <span data-slot="option-indicator"><x-ui::icon name="check" /></span>
        @endif
        @if (($optionSlot ?? '') === 'command-item' && empty($option['shortcut']))
            <x-ui::icon name="check" class="ui-command-check" />
        @endif
    </button>
    @if ($option['separatorAfter'] ?? false)
        <div data-slot="option-separator" role="separator"></div>
    @endif
@endforeach
