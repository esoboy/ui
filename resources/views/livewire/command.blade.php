<div x-data="uiCommand"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except('name')->class('ui-command-body')->merge(['data-size' => $size]) }}>
    <div class="ui-command-input-wrapper">
        <x-ui::input-group :size="$size">
            <input id="{{ $this->getId() }}-query" wire:model.live.debounce.200ms="query" type="search" role="combobox" aria-expanded="true" aria-autocomplete="list" aria-controls="{{ $this->getId() }}-list"
                x-on:keydown.down="navigate($event, true)" x-on:keydown.up="navigate($event, false)" x-on:keydown.enter="navigate($event)"
                x-on:input="chooseOnUpdate = false" x-on:blur="chooseOnUpdate = false" placeholder="{{ $placeholder !== '' ? $placeholder : __('ui::ui.search') }}"
                {{ $attributes->whereStartsWith('aria-')->merge(['aria-label' => __('ui::ui.search')])->class('ui-command-input') }} @disabled($disabled) />
            <x-ui::input-group.addon><x-ui::icon name="search" /></x-ui::input-group.addon>
        </x-ui::input-group>
    </div>
    <x-ui::scroll-area class="ui-list-scroll" tabindex="-1">
        <div class="ui-command-list" role="listbox" id="{{ $this->getId() }}-list" data-query="{{ $query }}" aria-multiselectable="{{ $multiple ? 'true' : 'false' }}">
            @foreach(collect($this->results)->groupBy(fn ($option) => $option['group'] ?? '') as $group => $choices)
                @unless($loop->first)<div wire:key="separator-{{ $group }}" class="ui-command-separator" role="separator"></div>@endunless
                <div wire:key="group-{{ $group }}" class="ui-command-group" role="group" @if($group !== '') aria-label="{{ $group }}" @endif>
                    @if($group !== '')
                        <div class="ui-command-group-heading">{{ $group }}</div>
                    @endif
                    @include('ui::livewire.partials.options', ['choices' => $choices, 'optionSlot' => 'command-item', 'showGroupLabels' => false, 'retainInputFocus' => true])
                </div>
            @endforeach
        </div>
        @if ($this->results === [])
            @include('ui::partials.empty-state', ['emptyMessage' => trim($query) !== '' ? 'matches' : 'commands'])
        @endif
    </x-ui::scroll-area>
    @include('ui::livewire.partials.selection-input')
</div>
