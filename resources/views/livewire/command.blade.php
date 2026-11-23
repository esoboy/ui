<div x-data="uiOptions" x-on:keydown="navigate($event)"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except('name')->class('ui-command-body') }}>
    <div class="ui-command-input-wrapper">
        <x-ui::input-group>
            <input id="{{ $this->getId() }}-query" wire:model.live.debounce.200ms="query" type="search" placeholder="{{ $placeholder !== '' ? $placeholder : __('ui::ui.search') }}"
                {{ $attributes->whereStartsWith('aria-')->merge(['aria-label' => __('ui::ui.search')])->class('ui-command-input') }} @disabled($disabled) />
            <x-ui::input-group.addon><x-ui::icon name="search" /></x-ui::input-group.addon>
        </x-ui::input-group>
    </div>
    <x-ui::scroll-area class="ui-list-scroll" tabindex="-1">
        <div class="ui-command-list" role="listbox" aria-multiselectable="{{ $multiple ? 'true' : 'false' }}">
            @foreach(collect($this->results)->groupBy(fn ($option) => $option['group'] ?? '') as $group => $choices)
                @unless($loop->first)<div class="ui-command-separator" role="separator"></div>@endunless
                @if($group !== '')
                    <div class="ui-command-group" role="group" aria-label="{{ $group }}">
                        <div class="ui-command-group-heading">{{ $group }}</div>
                        @include('ui::livewire.partials.options', ['choices' => $choices, 'optionSlot' => 'command-item', 'showGroupLabels' => false])
                    </div>
                @else
                    <div class="ui-command-group" role="group">
                        @include('ui::livewire.partials.options', ['choices' => $choices, 'optionSlot' => 'command-item', 'showGroupLabels' => false])
                    </div>
                @endif
            @endforeach
        </div>
        @if ($this->results === [])
            @include('ui::partials.empty-state', ['emptyMessage' => trim($query) !== '' ? 'matches' : 'commands'])
        @endif
    </x-ui::scroll-area>
    @include('ui::livewire.partials.selection-input')
</div>
