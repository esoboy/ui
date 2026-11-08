@foreach ($nodes as $node)
    @php
        $id = (string) $node['value'];
        $disabled = $ancestorDisabled || ! empty($node['disabled']);
        $children = $node['children'] ?? [];
        $parent = $children !== [] || ! empty($node['lazy']);
        $open = $parent && (in_array($id, $expanded, true) || $query !== '');
        $checked = ($settings['checkable'] ?? false) ? $this->checked($id) : null;
    @endphp
    <li role="treeitem" tabindex="{{ $id === (string) array_key_first($this->index()) ? 0 : -1 }}" wire:key="tree-item-{{ $id }}" data-slot="tree-item" data-value="{{ $id }}" aria-level="{{ $level }}" @if ($parent) aria-expanded="{{ $open ? 'true' : 'false' }}" @endif @if ($disabled) aria-disabled="true" @endif @if ($checked !== null) aria-checked="{{ $checked }}" @else aria-selected="{{ (string) $value === $id ? 'true' : 'false' }}" @endif @if (! $this->matches($node)) hidden @endif @if (! empty($node['lazy']) && $children === []) data-lazy @endif>
        <div data-slot="tree-item-row" wire:click="choose({{ \Illuminate\Support\Js::from($id) }})">
            <span data-slot="tree-item-toggle" aria-hidden="true" @if ($parent) wire:click.stop="expand({{ \Illuminate\Support\Js::from($id) }})" @endif>@if ($parent)<x-ui::icon name="chevron-right" />@endif @if (! empty($node['lazy']) && $children === [])<x-ui::spinner data-tree-item-spinner wire:loading wire:target="expand({{ \Illuminate\Support\Js::from($id) }})" />@endif</span>
            @if ($settings['checkable'] ?? false)
                <x-ui::checkbox data-tree-check tabindex="-1" aria-hidden="true" :disabled="$disabled" :checked="$checked === 'true'" :x-effect="'$el.indeterminate = '.json_encode($checked === 'mixed')" />
            @endif
            @if (! empty($node['icon']))<span data-slot="tree-item-icon"><x-ui::icon :name="$node['icon']" /></span>@endif
            <span data-slot="tree-item-label">{{ isset($settings['nodeView']) ? view($settings['nodeView'], ['node' => $node, 'level' => $level, 'checked' => $checked]) : ($node['label'] ?? $id) }}</span>
        </div>
        @if ($parent)
            <ul role="group" data-slot="tree-group" @if (! $open) hidden @endif>
                @include('ui::livewire.tree-nodes', ['nodes' => $children, 'level' => $level + 1, 'ancestorDisabled' => $disabled])
            </ul>
        @endif
    </li>
@endforeach
