<div data-esoboy-ui {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'nodes', $attributes)->whereDoesntStartWith('wire:model')->except('name') }}>
    @if ($this->visibleRows === [])
        @include('ui::partials.empty-state', ['emptyMessage' => 'nodes', 'emptySize' => 'default', 'emptyIcon' => 'folder-tree'])
    @else
        @include('ui::livewire.partials.tree')
    @endif
    @include('ui::livewire.partials.selection-input')
</div>
