@php
    $attributes = \Esoboy\Ui\Support\ComponentColor::attributes($color, 'options', $attributes);
@endphp
<div data-esoboy-ui data-slot="combobox" data-disabled="{{ $disabled ? 'true' : 'false' }}" data-ui-overlay x-data="uiCombobox" x-on:keydown="navigate($event)"
    @if ($paginated ?? false) data-confirmed-icon="{{ $this->current }}" x-on:input="editIconQuery($event.target.value)" x-on:click="chooseIcon($event)" @endif
    x-on:pointerdown.capture="if (!opened) $el.toggleAttribute('data-touch', $event.pointerType === 'touch')"
    x-on:keydown.capture="if (!opened) $el.removeAttribute('data-touch')"
    x-on:focusout="if (!$root.contains($event.relatedTarget)) close()"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'aria-label', 'aria-labelledby']) }}>
    @if($searchInPopup)
        @php($emptyLabel = $placeholder !== '' ? $placeholder : __('ui::ui.select'))
        @php($triggerLabel = $this->label !== '' ? $this->label : $emptyLabel)
        <x-ui::popover.trigger><x-ui::button variant="outline" class="ui-combobox-trigger" data-ui-anchor-box :disabled="$disabled" :id="$attributes->get('id')" {{ $attributes->whereStartsWith('aria-') }}>
            @if ($paginated ?? false)
                <output role="presentation" x-ref="iconCaption" data-placeholder="{{ $emptyLabel }}">{{ $triggerLabel }}</output>
            @else
                {{ $triggerLabel }}
            @endif
            <x-ui::icon name="chevron-down" class="ui-select-chevron" />
        </x-ui::button></x-ui::popover.trigger>
    @else
        @include('ui::livewire.partials.combobox-input')
    @endif
    <div id="{{ $this->getId() }}-popup" data-esoboy-ui data-slot="combobox-content" data-match-trigger data-side="{{ $side }}" data-preferred-side="{{ $side }}" data-align="{{ $align }}" data-align-offset="{{ $alignOffset }}" data-side-offset="{{ $sideOffset }}" data-align-trigger="{{ $alignItemWithTrigger ? 'true' : 'false' }}" data-ui-panel popover="auto" class="ui-popup ui-list-popup {{ $popupClass }}"
        x-on:toggle="toggled($event); if ($event.target === $el && $event.newState === 'closed') {{ ($paginated ?? false) ? 'finishBrowsing()' : '$wire.finishSearch()' }}"
        x-on:pointermove="highlightOption($event.target.closest('[role=option]'), true)" x-on:pointerleave="if (pointingToOption) highlightOption(null)">
        @if($searchInPopup)@include('ui::livewire.partials.combobox-input')@endif
        @php($resultPage = ($paginated ?? false) ? $this->resultPage : null)
        @if ($this->results === [])
            <div id="{{ $this->getId() }}-list" role="listbox" aria-multiselectable="{{ $multiple ? 'true' : 'false' }}"></div>
            @include('ui::partials.empty-state', ['emptyMessage' => $searching && trim($query) !== '' ? 'matches' : 'options'])
        @else
            <x-ui::scroll-area class="ui-list-scroll" tabindex="-1" wire:key="combobox-results-{{ md5($query.($this->searching ? '-searching' : '-selected')) }}-{{ $resultPage ?? 1 }}">
                <div id="{{ $this->getId() }}-list" data-esoboy-ui data-slot="combobox-list" role="listbox" aria-multiselectable="{{ $multiple ? 'true' : 'false' }}" data-auto-highlight="{{ $autoHighlight && $this->searching && $query !== '' ? 'true' : 'false' }}"
                    x-init="$nextTick(() => initializeOptions($el))"
                    @isset($resultPage) x-on:mousedown.prevent @endisset
                    aria-busy="false" wire:loading.attr="aria-busy">
                    @isset($resultPage)
                        @include('ui::livewire.partials.icon-select-options')
                    @else
                        @include('ui::livewire.partials.options', ['choices' => $this->results, 'popup' => true, 'optionLabelCompact' => false, 'retainInputFocus' => true])
                    @endisset
                </div>
            </x-ui::scroll-area>
            @if (isset($resultPage) && $this->lastResultPage > 1)
                @include('ui::livewire.partials.icon-select-pagination')
            @endif
        @endif
    </div>
    @if (($paginated ?? false) && $attributes->get('name'))
        <input type="hidden" x-ref="iconField" name="{{ $attributes->get('name') }}" value="{{ $this->current }}"
            {{ $attributes->only('form') }} @disabled($disabled || $this->current === null)>
    @else
        @include('ui::livewire.partials.selection-input')
    @endif
</div>
