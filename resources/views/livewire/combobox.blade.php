<div data-disabled="{{ $disabled ? 'true' : 'false' }}" @if($paginated ?? false) data-confirmed-icon="{{ $this->current }}" @endif data-ui-overlay x-data="uiCombobox" x-on:keydown="navigate($event)"
    x-on:pointerdown.capture="if (!opened) $el.toggleAttribute('data-touch', $event.pointerType === 'touch')"
    x-on:keydown.capture="if (!opened) $el.removeAttribute('data-touch')"
    x-on:focusout="if (!$root.contains($event.relatedTarget)) close()"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'aria-label', 'aria-labelledby'])->class('ui-combobox-body') }}>
    @if($searchInPopup)
        @php($emptyLabel = $placeholder !== '' ? $placeholder : __('ui::ui.select'))
        @php($triggerLabel = $this->label !== '' ? $this->label : $emptyLabel)
        <x-ui::popover.trigger><x-ui::button variant="outline" class="ui-combobox-trigger" :size="$size" data-ui-anchor-box :disabled="$disabled" :id="$attributes->get('id')" {{ $attributes->whereStartsWith('aria-') }}>
            @if ($paginated ?? false)
                <output role="presentation" wire:text="{{ $mode === 'controlled' ? 'externalValue' : 'value' }} ?? @js($emptyLabel)">{{ $triggerLabel }}</output>
            @else
                {{ $triggerLabel }}
            @endif
            <x-ui::icon name="chevron-down" class="ui-select-chevron" />
        </x-ui::button></x-ui::popover.trigger>
    @else
        @include('ui::livewire.partials.combobox-input')
    @endif
    <div id="{{ $this->getId() }}-popup" @if ($paginated ?? false) data-match-trigger="0" @endif
        data-side="{{ $side }}" data-preferred-side="{{ $side }}" data-align="{{ $align }}" data-align-offset="{{ $alignOffset }}" data-side-offset="{{ $sideOffset }}" data-align-trigger="{{ $alignItemWithTrigger ? 'true' : 'false' }}" data-ui-panel popover="auto"
        @class(['ui-combobox-content ui-popup ui-list-popup', 'ui-icon-select-content ui-popup-centered' => $paginated ?? false, $popupClass])
        x-on:toggle="finishBrowsing($event, @js($paginated ?? false))"
        x-on:pointermove="highlightOption($event.target.closest('[role=option]'), true)" x-on:pointerleave="if (pointingToOption) highlightOption(null)">
        @if($searchInPopup)@include('ui::livewire.partials.combobox-input')@endif
        @php($resultPage = ($paginated ?? false) ? $this->resultPage : null)
        @if ($this->results === [])
            <div id="{{ $this->getId() }}-list" role="listbox" aria-multiselectable="{{ $multiple ? 'true' : 'false' }}"></div>
            @include('ui::partials.empty-state', ['emptyMessage' => $searching && trim($query) !== '' ? 'matches' : 'options'])
        @else
            <x-ui::scroll-area class="ui-list-scroll" tabindex="-1" wire:key="combobox-results-{{ md5($query.($this->searching ? '-searching' : '-selected')) }}-{{ $resultPage ?? 1 }}">
                <div class="ui-combobox-list" id="{{ $this->getId() }}-list" role="listbox" aria-multiselectable="{{ $multiple ? 'true' : 'false' }}" data-auto-highlight="{{ $autoHighlight && $this->searching && $query !== '' ? 'true' : 'false' }}"
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
        <input type="hidden"  name="{{ $attributes->get('name') }}" value="{{ $this->current }}" wire:bind:value="{{ $mode === 'controlled' ? 'externalValue' : 'value' }}"
            {{ $attributes->only('form') }} @disabled($disabled || $this->current === null) wire:bind:disabled="disabled || {{ $mode === 'controlled' ? 'externalValue' : 'value' }} == null">
    @else
        @include('ui::livewire.partials.selection-input')
    @endif
</div>

@if ($paginated ?? false)
    @script
    <script>
        $wire.$js('pick', async (name) => {
            if ($wire.disabled || $wire.readonly) return
            if ($wire.mode === 'controlled') return $wire.choose(name)
            const confirmed = $wire.$el.dataset.confirmedIcon || null
            $wire.query = $wire.searchInPopup ? '' : name
            $wire.value = name
            try {
                await $wire.confirmIcon(name)
            } catch {
                if ($wire.$el.isConnected && $wire.value === name) {
                    $wire.value = confirmed
                    $wire.query = $wire.searchInPopup ? '' : (confirmed ?? '')
                }
            }
        })
    </script>
    @endscript
@endif
