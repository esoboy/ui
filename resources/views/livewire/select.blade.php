@php
    $attributes = \Esoboy\Ui\Support\ComponentColor::attributes($color, 'options', $attributes);
@endphp
<div data-esoboy-ui data-slot="select" data-ui-overlay x-data="uiPopover"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'class', 'aria-label', 'aria-labelledby']) }}>
    <x-ui::select.trigger x-on:pointerdown.capture="$el.toggleAttribute('data-touch', $event.pointerType === 'touch')" x-on:keydown.capture="$el.removeAttribute('data-touch')">
        <x-ui::button variant="outline" :size="$size" :disabled="$disabled" :id="$attributes->get('id')" :class="$attributes->get('class')" {{ $attributes->whereStartsWith('aria-') }}
            aria-haspopup="listbox" data-slot="select-control" :data-size="$size" :data-placeholder="$this->label === '' && $displayValue === null ? true : null">
            @php($displayOption = count($this->selected) <= 1 ? collect($this->choices)->first(fn ($option) => $this->isSelected($option['value'])) : null)
            <span data-esoboy-ui data-slot="select-value">
                @if($displayValue !== null){{ $displayValue }}
                @elseif(isset($displayOption['description']))
                    @include('ui::livewire.partials.option-label', ['option' => $displayOption])
                @else @isset($displayOption['icon'])<x-ui::icon :name="$displayOption['icon']" />@endisset{{ $this->label !== '' ? $this->label : ($placeholder !== '' ? $placeholder : ($displayOption['label'] ?? __('ui::ui.select'))) }}@endif
            </span>
            <x-ui::icon name="chevron-down" class="ui-select-chevron" />
        </x-ui::button>
    </x-ui::select.trigger>
    <div id="{{ $this->getId() }}-listbox" data-esoboy-ui data-slot="select-content" data-match-trigger data-side="{{ $side }}" data-preferred-side="{{ $side }}" data-align="{{ $align }}" data-side-offset="{{ $sideOffset }}" data-align-offset="{{ $alignOffset }}" data-align-trigger="{{ $alignItemWithTrigger ? 'true' : 'false' }}" data-ui-panel popover="auto" role="listbox" tabindex="-1"
        aria-multiselectable="{{ $multiple ? 'true' : 'false' }}" class="ui-popup ui-list-popup {{ $popupClass }}" x-on:keydown="navigate($event)"
        x-on:toggle="toggled($event)"
        x-on:pointermove="if ($event.pointerType === 'mouse') { pointerFocus = true; $event.target.closest('[role=option]:not(:disabled)')?.focus({ preventScroll: true }) }"
        x-on:pointerleave="if (pointerFocus) $el.focus({ preventScroll: true })">
        <x-ui::scroll-area class="ui-list-scroll" tabindex="-1">
            @include('ui::livewire.partials.select-list')
        </x-ui::scroll-area>
    </div>
    @include('ui::livewire.partials.selection-input')
</div>
