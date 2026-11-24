<div class="ui-tree-body" data-size="{{ $size }}" role="tree" aria-multiselectable="{{ $multiple ? 'true' : 'false' }}" @if ($selectParents) data-select-parents @endif
    x-data="uiOptions('[data-node-action]')" x-on:keydown.stop="navigate($event)">
    @foreach ($this->visibleRows as $row)
        @php($active = $row['branch'] && in_array($row['value'], $expanded, true))
        <div class="ui-tree-row" role="treeitem" aria-level="{{ $row['level'] }}"
            @if (! $row['branch'] || $selectParents) aria-selected="{{ $this->isSelected($row['value']) ? 'true' : 'false' }}" @endif
            @if ($row['branch']) aria-expanded="{{ $active ? 'true' : 'false' }}" @endif
            data-node-row style="--level: {{ $row['level'] }}" wire:key="node-{{ $row['value'] }}">
            @include('ui::livewire.partials.tree-node', ['browseDisabled' => $disabled, 'collapsible' => true])
        </div>
    @endforeach
</div>
