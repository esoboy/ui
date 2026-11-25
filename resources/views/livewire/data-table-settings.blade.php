<x-ui::popover x-on:ui-column-focus="$nextTick(() => { if (document.activeElement === document.body) $el.querySelector('[data-key=' + CSS.escape($event.detail.key) + '] [data-column-handle]')?.focus() })" :attributes="$attributes->whereDoesntStartWith('wire:model')->class('ui-data-table-column-settings')">
    <x-ui::popover.trigger>
        <x-ui::button class="ui-table-columns-trigger" variant="outline" size="sm"><x-ui::icon name="settings-2" />{{ __('ui::ui.table_columns') }}</x-ui::button>
    </x-ui::popover.trigger>
    <x-ui::popover.content align="end" class="ui-table-columns ui-popup-centered" :aria-label="__('ui::ui.table_columns')">
        <header>
            <span>{{ __('ui::ui.table_columns') }}</span>
            <span>{{ __('ui::ui.table_columns_visible', ['visible' => count($layout->visible), 'total' => count($layout->ordered)]) }}</span>
        </header>
        <x-ui::separator />
        <fieldset aria-label="{{ __('ui::ui.table_columns') }}" wire:sort="reorder">
            @foreach ($layout->ordered as $key => $column)
                @php($visible = isset($layout->visible[$key]))
                <div
                    wire:key="{{ $this->getId() }}-setting-{{ $key }}" data-key="{{ $key }}"
                    @if ($column['reorderable']) data-movable wire:sort:item="{{ $key }}" @endif>
                    <label>
                        <x-ui::checkbox :value="$key" :wire:change="'toggleColumn('.\Illuminate\Support\Js::from($key).')'"
                            :checked="$visible" :x-bind:checked="$visible ? 'true' : 'false'" :disabled="! $layout->canToggle($key)" />
                        <span>{{ $column['label'] }}</span>
                    </label>
                    @if ($column['fixed'])
                        <span class="ui-table-column-pin"><x-ui::icon name="pin" :size="12" />{{ __('ui::ui.table_fixed_'.$column['fixed']) }}</span>
                    @elseif ($column['reorderable'])
                        <x-ui::button variant="ghost" size="icon-sm"
                            data-column-handle wire:sort:handle :aria-label="__('ui::ui.table_reorder_column', ['label' => $column['label']])"
                            :title="__('ui::ui.table_reorder_hint')" aria-keyshortcuts="ArrowUp ArrowDown"
                             :wire:keydown.up.stop.prevent="'move('.\Illuminate\Support\Js::from($key).', -1)'" :wire:keydown.down.stop.prevent="'move('.\Illuminate\Support\Js::from($key).', 1)'">
                            <x-ui::icon name="grip-vertical" />
                        </x-ui::button>
                    @endif
                </div>
            @endforeach
        </fieldset>
        <x-ui::separator />
        <footer>
            <x-ui::button variant="ghost" size="sm" :disabled="! $layout->isCustomized"
                wire:click="resetLayout">{{ __('ui::ui.table_reset_layout') }}</x-ui::button>
            <x-ui::popover.close><x-ui::button variant="outline" size="sm">{{ __('ui::ui.table_done') }}</x-ui::button></x-ui::popover.close>
        </footer>
    </x-ui::popover.content>
</x-ui::popover>
