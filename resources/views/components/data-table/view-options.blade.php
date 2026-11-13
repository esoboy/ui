@blaze

@use('Esoboy\Ui\Support\Model')
@use('Esoboy\Ui\View\DataTable')
@use('Illuminate\Support\Js')

@props(['columns'])

@php
    $owner = Model::component();
    $movable = DataTable::movableColumns($columns);
    $visible = DataTable::displayedColumns($columns);
@endphp

@if ($movable && $owner && method_exists($owner, 'orderedColumns'))
    <x-ui::dropdown-menu>
        <x-ui::dropdown-menu.trigger>
            <x-ui::button variant="outline" size="sm" :attributes="$attributes->merge(['data-data-table-view-options' => true])">
                <x-ui::icon name="settings-2" />
                {{ __('ui::data-table.view') }}
            </x-ui::button>
        </x-ui::dropdown-menu.trigger>
        <x-ui::dropdown-menu.content align="end" class="w-[150px]">
            <x-ui::dropdown-menu.label>{{ __('ui::data-table.toggle_columns') }}</x-ui::dropdown-menu.label>
            <x-ui::dropdown-menu.separator />
            <div data-slot="data-table-column-list" wire:sort="moveColumn" wire:sort:group-id="{{ implode(',', array_keys($movable)) }}">
                @foreach ($owner->orderedColumns($movable) as $key => $column)
                    <div data-slot="data-table-column-option" wire:sort:item="{{ $key }}" wire:key="data-table-column-{{ $key }}">
                        <span data-slot="data-table-column-handle" wire:sort:handle aria-hidden="true"><x-ui::icon name="grip-vertical" /></span>
                        <x-ui::dropdown-menu.checkbox-item
                            :checked="isset($visible[$key])"
                            :disabled="isset($visible[$key]) && count($visible) === 1"
                            :wire:click="'toggleColumn('.Js::from($key).')'"
                        >{{ $column['title'] }}</x-ui::dropdown-menu.checkbox-item>
                    </div>
                @endforeach
            </div>
            @if ($owner->columnOrder !== [] || $owner->hiddenColumns !== [])
                <x-ui::dropdown-menu.separator />
                <x-ui::dropdown-menu.item wire:click="resetColumns" data-data-table-reset-columns>{{ __('ui::data-table.reset_columns') }}</x-ui::dropdown-menu.item>
            @endif
        </x-ui::dropdown-menu.content>
    </x-ui::dropdown-menu>
@endif
