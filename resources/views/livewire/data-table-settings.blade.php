<x-ui::popover x-on:ui-column-focus="const root = $event.currentTarget, key = $event.detail.key; requestAnimationFrame(() => root.querySelector('[data-key=' + CSS.escape(key) + '] [data-column-handle]')?.focus({ preventScroll: true }))" :attributes="$attributes->whereDoesntStartWith('wire:model')->class('ui-data-table-column-settings')">
    @php($hasLayout = is_array($this->value) && ! array_is_list($this->value))
    @include('ui::livewire.partials.model-field')
    {{-- PHP encodes the initial empty layout as []. Make its first named member on edit. --}}
    <ui-model-field hidden id="{{ $this->fieldId() }}-order" data-initial="{{ json_encode($movable, JSON_THROW_ON_ERROR) }}"
        wire:key="order-field-{{ (int) $hasLayout }}"
        @if ($hasLayout) {{ $this->fieldBinding('order') }}
        @else x-on:input="$el.previousElementSibling.edit({ ...$el.previousElementSibling.value, order: $event.detail })" @endif
        wire:input="afterEdit($event.target.dataset.focusColumn ?? null)"></ui-model-field>
    <select hidden multiple id="{{ $this->fieldId() }}-hidden" wire:key="hidden-{{ (int) $hasLayout }}-{{ md5(serialize(array_keys(array_diff_key($layout->ordered, $layout->visible)))) }}"
        @if ($hasLayout) {{ $this->fieldBinding('hidden') }}
        @else x-on:change="const field = document.getElementById(@js($this->fieldId())); field.edit({ ...field.value, hidden: Array.from($el.selectedOptions, option => option.value) })" @endif>
        @foreach ($layout->ordered as $key => $column)
            @if ($layout->canToggle($key))
                <option wire:key="hideable-{{ $key }}" name="{{ $key }}" value="{{ $key }}" @selected(! isset($layout->visible[$key]))>{{ $key }}</option>
            @endif
        @endforeach
    </select>
    <x-ui::popover.trigger>
        <x-ui::button class="ui-table-columns-trigger" variant="outline" size="sm"><x-ui::icon name="settings-2" />{{ __('ui::ui.table_columns') }}</x-ui::button>
    </x-ui::popover.trigger>
    <x-ui::popover.content align="end" class="ui-table-columns ui-popup-centered" :aria-label="__('ui::ui.table_columns')">
        <header>
            <span>{{ __('ui::ui.table_columns') }}</span>
            <span>{{ __('ui::ui.table_columns_visible', ['visible' => count($layout->visible), 'total' => count($layout->ordered)]) }}</span>
        </header>
        <x-ui::separator />
        <fieldset aria-label="{{ __('ui::ui.table_columns') }}" x-sort="document.getElementById(@js($this->fieldId().'-order')).edit(Array.from($el.querySelectorAll('[data-movable]'), row => row.dataset.key))">
            @foreach ($layout->ordered as $key => $column)
                @php($visible = isset($layout->visible[$key]))
                <div
                    wire:key="{{ $this->getId() }}-setting-{{ $key }}" data-key="{{ $key }}"
                    @if ($column['reorderable']) data-movable x-sort:item="@js($key)" @endif>
                    <label>
                        <x-ui::checkbox :value="$key" :data-field="$this->fieldId().'-hidden'" x-on:change="const field = document.getElementById($el.dataset.field); field.options.namedItem($el.value).selected = !$el.checked; field.dispatchEvent(new Event('change', { bubbles: true }))"
                            wire:key="visibility-{{ $key }}-{{ (int) $visible }}" :checked="$visible" :disabled="! $layout->canToggle($key)" />
                        <span>{{ $column['label'] }}</span>
                    </label>
                    @if ($column['fixed'])
                        <span class="ui-table-column-pin"><x-ui::icon name="pin" :size="12" />{{ __('ui::ui.table_fixed_'.$column['fixed']) }}</span>
                    @elseif ($column['reorderable'])
                        <x-ui::button variant="ghost" size="icon-sm"
                            data-column-handle x-sort:handle :aria-label="__('ui::ui.table_reorder_column', ['label' => $column['label']])"
                            :title="__('ui::ui.table_reorder_hint')" aria-keyshortcuts="ArrowUp ArrowDown"
                             :data-field="$this->fieldId().'-order'" :data-up="json_encode($this->orderAfter($key, array_search($key, $movable, true) - 1))"
                             :data-down="json_encode($this->orderAfter($key, array_search($key, $movable, true) + 1))"
                             x-on:keydown.up.stop.prevent="const field = document.getElementById($el.dataset.field); field.dataset.focusColumn = $el.closest('[data-key]').dataset.key; field.edit(JSON.parse($el.dataset.up))"
                             x-on:keydown.down.stop.prevent="const field = document.getElementById($el.dataset.field); field.dataset.focusColumn = $el.closest('[data-key]').dataset.key; field.edit(JSON.parse($el.dataset.down))">
                            <x-ui::icon name="grip-vertical" />
                        </x-ui::button>
                    @endif
                </div>
            @endforeach
        </fieldset>
        <x-ui::separator />
        <footer>
            <x-ui::button variant="ghost" size="sm" :disabled="! $layout->isCustomized"
                :attributes="$this->fieldEdit(['order' => [], 'hidden' => []])">{{ __('ui::ui.table_reset_layout') }}</x-ui::button>
            <x-ui::popover.close><x-ui::button variant="outline" size="sm">{{ __('ui::ui.table_done') }}</x-ui::button></x-ui::popover.close>
        </footer>
    </x-ui::popover.content>
</x-ui::popover>
