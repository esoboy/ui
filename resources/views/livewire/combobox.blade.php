<div data-slot="combobox" data-disabled="{{ $disabled ? 'true' : 'false' }}" data-ui-overlay x-data="uiPopover" x-on:keydown="navigate($event)"
    x-on:focusout="if (!$root.contains($event.relatedTarget)) close()"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'aria-label', 'aria-labelledby']) }}>
    @if($searchInPopup)
        <x-ui::popover.trigger><x-ui::button variant="outline" class="ui-combobox-trigger" data-ui-anchor-box :disabled="$disabled" :id="$attributes->get('id')" {{ $attributes->whereStartsWith('aria-') }}>{{ $this->label !== '' ? $this->label : ($placeholder !== '' ? $placeholder : __('ui::ui.select')) }}<x-ui::icon name="chevron-down" class="ui-select-chevron" /></x-ui::button></x-ui::popover.trigger>
    @else
        @include('ui::livewire.partials.combobox-input')
    @endif
    <div id="{{ $this->getId() }}-popup" data-slot="combobox-content" data-match-trigger data-side="{{ $side }}" data-preferred-side="{{ $side }}" data-align="{{ $align }}" data-align-offset="{{ $alignOffset }}" data-side-offset="{{ $sideOffset }}" data-ui-panel popover="auto" class="ui-popup ui-list-popup {{ $popupClass }}"
        x-on:toggle="toggled($event); if ($event.target === $el && $event.newState === 'closed') $wire.finishSearch()" x-on:pointermove="highlightOption($event.target.closest('[role=option]'), true)" x-on:pointerleave="if (pointingToOption) highlightOption(null)">
        @if($searchInPopup)@include('ui::livewire.partials.combobox-input')@endif
        @if ($this->results === [])
            <div id="{{ $this->getId() }}-list" role="listbox" aria-multiselectable="{{ $multiple ? 'true' : 'false' }}"></div>
            @include('ui::partials.empty-state', ['emptyMessage' => $searching && trim($query) !== '' ? 'matches' : 'options'])
        @else
            <x-ui::scroll-area class="ui-list-scroll" tabindex="-1" wire:key="combobox-results-{{ md5($query.($this->searching ? '-searching' : '-selected')) }}">
                <div id="{{ $this->getId() }}-list" data-slot="combobox-list" role="listbox" aria-multiselectable="{{ $multiple ? 'true' : 'false' }}" data-auto-highlight="{{ $autoHighlight && $this->searching && $query !== '' ? 'true' : 'false' }}"
                    x-init="if ($el.dataset.autoHighlight === 'true') $nextTick(() => highlightOption($el.querySelector('[role=option]:not(:disabled)')))"
                    aria-busy="false" wire:loading.attr="aria-busy">
                    @include('ui::livewire.partials.options', ['choices' => $this->results, 'popup' => true, 'optionLabelCompact' => false, 'retainInputFocus' => true])
                </div>
            </x-ui::scroll-area>
        @endif
    </div>
    @include('ui::livewire.partials.selection-input')
</div>
