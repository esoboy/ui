@php
    $attributes = \Esoboy\Ui\Support\ComponentColor::attributes($color, 'nodes', $attributes);
@endphp
<div data-esoboy-ui data-slot="cascader" data-ui-overlay x-data="uiPopover"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'aria-'])->except(['id', 'name', 'aria-label', 'aria-labelledby']) }}>
    <x-ui::select.trigger><x-ui::button variant="outline" :disabled="$disabled" :id="$attributes->get('id')"
            {{ $attributes->whereStartsWith('aria-') }} aria-haspopup="dialog"
            data-slot="select-control" data-size="default" :data-placeholder="$this->label === '' ? true : null"><span data-esoboy-ui data-slot="select-value">{{ $this->label !== '' ? $this->label : ($placeholder !== '' ? $placeholder : __('ui::ui.select')) }}</span><x-ui::icon
                name="chevron-down" class="ui-select-chevron" /></x-ui::button></x-ui::select.trigger>
    <x-ui::popover.content id="{{ $this->getId() }}-popup" align="start" data-match-trigger class="ui-list-popup">
        <x-ui::scroll-area orientation="horizontal" class="ui-cascader-scroll" tabindex="-1">
            <div class="ui-cascader-columns">
                @forelse ($this->columns as $column)
                    <x-ui::scroll-area class="ui-list-scroll ui-cascader-column" tabindex="-1" wire:key="cascade-level-{{ $loop->index }}-{{ $column[0]['parent'] }}"
                        x-on:focusin="$el.scrollIntoView({ block: 'nearest', inline: 'nearest', container: 'nearest' })">
                        <div role="group" aria-label="{{ __('ui::ui.node_level', ['level' => $loop->iteration]) }}"
                            x-data="uiOptions('[data-node-action]')" x-on:keydown.stop="navigate($event)">
                            @foreach ($column as $row)
                                @php($active = $row['branch'] && in_array($row['value'], $path, true))
                                <div data-esoboy-ui data-node-row wire:key="cascade-{{ $row['value'] }}">
                                    @include('ui::livewire.partials.tree-node', [
                                        'browseDisabled' => $disabled || $readonly || $row['disabled'], 'collapsible' => false,
                                    ])
                                </div>
                            @endforeach
                        </div>
                    </x-ui::scroll-area>
                @empty
                    @include('ui::partials.empty-state', ['emptyMessage' => 'select_nodes'])
                @endforelse
            </div>
        </x-ui::scroll-area>
    </x-ui::popover.content>
    @include('ui::livewire.partials.selection-input')
</div>
