<div data-ui-overlay x-data="uiPopover"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'aria-label', 'aria-labelledby'])->class('ui-tree-select-body') }}>
    <x-ui::select.trigger><x-ui::button variant="outline" :size="$size" :disabled="$disabled" :id="$attributes->get('id')"
            {{ $attributes->whereStartsWith('aria-')->class('ui-select-control') }} aria-haspopup="tree" :data-size="$size"
           ><span>{{ $this->label !== '' ? $this->label : ($placeholder !== '' ? $placeholder : __('ui::ui.select')) }}</span><x-ui::icon
                name="chevron-down" /></x-ui::button></x-ui::select.trigger>
    <x-ui::popover.content id="{{ $this->getId() }}-popup" align="start" data-match-trigger class="ui-list-popup">
        <x-ui::scroll-area class="ui-list-scroll" tabindex="-1">
            @if ($this->visibleRows === [])
                @include('ui::partials.empty-state', ['emptyMessage' => 'select_nodes'])
            @else
                @include('ui::livewire.partials.tree')
            @endif
        </x-ui::scroll-area>
    </x-ui::popover.content>
    @include('ui::livewire.partials.selection-input')
</div>
