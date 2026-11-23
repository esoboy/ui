@aware(['dataTable'])
@if ($dataTable->layoutModel)
    <x-ui::popover :attributes="$attributes->class('ui-data-table-column-settings')">
        <x-ui::popover.trigger>
            <x-ui::button class="ui-table-columns-trigger" variant="outline" size="sm"><x-ui::icon name="settings-2" />{{ __('ui::ui.table_columns') }}</x-ui::button>
        </x-ui::popover.trigger>
        <x-ui::popover.content align="end" class="ui-table-columns" :aria-label="__('ui::ui.table_columns')">
            <header>
                <span>{{ __('ui::ui.table_columns') }}</span>
                <span>{{ __('ui::ui.table_columns_visible', ['visible' => count($dataTable->layout->visible), 'total' => count($dataTable->layout->ordered)]) }}</span>
            </header>
            <x-ui::separator />
            <fieldset
                aria-label="{{ __('ui::ui.table_columns') }}" data-model="{{ $dataTable->layoutModel }}"
                x-data="{
                    keys() { return Array.from(this.$root.querySelectorAll('[data-movable]'), row => row.dataset.key) },
                    save(order) { return this.$wire.$set(this.$root.dataset.model + '.order', order) },
                    async move(handle, step) {
                        const order = this.keys()
                        const from = order.indexOf(handle.closest('[data-key]').dataset.key)
                        const to = from + step
                        if (from < 0 || to < 0 || to >= order.length) return
                        order.splice(to, 0, order.splice(from, 1)[0])
                        await this.save(order)
                        if (document.activeElement === document.body) handle.focus()
                    }
                }"
                x-sort.ghost="save(keys())"
                x-sort:config="{ draggable: '[data-movable]', handle: '[data-column-handle]', onMove: e => e.related.hasAttribute('data-movable') }"
                x-on:change="
                    const boxes = [...$el.querySelectorAll('input[type=checkbox]')]
                    const fields = boxes.filter(box => box.hasAttribute('data-business'))
                    if (!(fields.length ? fields : boxes).some(box => box.checked)) {
                        $event.target.checked = true
                        return
                    }
                    $wire.$set($el.dataset.model + '.hidden', boxes.filter(box => !box.checked).map(box => box.value))
                ">
                @foreach ($dataTable->layout->ordered as $key => $column)
                    <div
                        wire:key="{{ $dataTable->layoutModel }}-setting-{{ $key }}" data-key="{{ $key }}"
                        @if ($column['reorderable']) data-movable x-sort:item="$el.dataset.key" @endif>
                        <label>
                            <x-ui::checkbox :value="$key" :data-business="$column['field'] !== null ? 'true' : null"
                                :checked="isset($dataTable->layout->visible[$key])" :disabled="! $dataTable->layout->canToggle($key)" />
                            <span>{{ $column['label'] }}</span>
                        </label>
                        @if ($column['fixed'])
                            <span class="ui-table-column-pin"><x-ui::icon name="pin" :size="12" />{{ __('ui::ui.table_fixed_'.$column['fixed']) }}</span>
                        @elseif ($column['reorderable'])
                            <x-ui::button variant="ghost" size="icon-sm"
                                data-column-handle :aria-label="__('ui::ui.table_reorder_column', ['label' => $column['label']])"
                                :title="__('ui::ui.table_reorder_hint')" aria-keyshortcuts="ArrowUp ArrowDown"
                                x-on:keydown.up.stop.prevent="move($el, -1)" x-on:keydown.down.stop.prevent="move($el, 1)">
                                <x-ui::icon name="grip-vertical" />
                            </x-ui::button>
                        @endif
                    </div>
                @endforeach
            </fieldset>
            <x-ui::separator />
            <footer>
                <x-ui::button variant="ghost" size="sm" :disabled="! $dataTable->layout->isCustomized"
                    wire:click="{{ $dataTable->setValue($dataTable->layoutModel, ['order' => [], 'hidden' => []]) }}">{{ __('ui::ui.table_reset_layout') }}</x-ui::button>
                <x-ui::popover.close><x-ui::button variant="outline" size="sm">{{ __('ui::ui.table_done') }}</x-ui::button></x-ui::popover.close>
            </footer>
        </x-ui::popover.content>
    </x-ui::popover>
@endif
