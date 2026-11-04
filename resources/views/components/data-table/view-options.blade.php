@blaze

@use('Esoboy\Ui\Support\Model')

{{-- columns: ['email' => 'Email', ...], the hideable columns and their labels as written. reorderable lets the user drag them into another order (Livewire's wire:sort), which visibleColumns() renders. --}}
@props([
    'columns' => [],
    'reorderable' => false,
])

@php
$table = Model::component();
$hidden = $table && property_exists($table, 'hiddenColumns') ? $table->hiddenColumns : [];
$reorderable = $reorderable && $table && method_exists($table, 'moveColumn');
$listed = $reorderable ? $table->orderedColumns($columns) : $columns;
$changed = $reorderable && ($table->columnOrder !== [] || $hidden !== []);
@endphp

<x-ui::dropdown-menu>
    <x-ui::dropdown-menu.trigger variant="outline" size="sm" :attributes="$attributes->merge(['data-data-table-view-options' => true])">
        <x-ui::icon name="settings-2" />
        {{ __('ui::data-table.view') }}
    </x-ui::dropdown-menu.trigger>
    <x-ui::dropdown-menu.content align="end" class="w-[150px]">
        <x-ui::dropdown-menu.label>{{ __('ui::data-table.toggle_columns') }}</x-ui::dropdown-menu.label>
        <x-ui::dropdown-menu.separator />
        @if ($reorderable)
        {{-- wire:sort calls moveColumn(column, position, group-id): the group id is the columns as written, which the server can't know on its own. --}}
        <div data-slot="data-table-column-list" wire:sort="moveColumn" wire:sort:group-id="{{ implode(',', array_keys($columns)) }}">
            @foreach ($listed as $column => $label)
            <div data-slot="data-table-column-option" wire:sort:item="{{ $column }}" wire:key="data-table-column-{{ $column }}">
                <span data-slot="data-table-column-handle" wire:sort:handle aria-hidden="true"><x-ui::icon name="grip-vertical" /></span>
                <x-ui::dropdown-menu.checkbox-item class="capitalize" :checked="! in_array($column, $hidden, true)" wire:click="toggleColumn('{{ $column }}')">{{ $label }}</x-ui::dropdown-menu.checkbox-item>
            </div>
            @endforeach
        </div>
        @if ($changed)
        <x-ui::dropdown-menu.separator />
        <x-ui::dropdown-menu.item wire:click="resetColumns" data-data-table-reset-columns>{{ __('ui::data-table.reset_columns') }}</x-ui::dropdown-menu.item>
        @endif
        @else
        @foreach ($columns as $column => $label)
        <x-ui::dropdown-menu.checkbox-item class="capitalize" :checked="! in_array($column, $hidden, true)" wire:click="toggleColumn('{{ $column }}')">{{ $label }}</x-ui::dropdown-menu.checkbox-item>
        @endforeach
        @endif
    </x-ui::dropdown-menu.content>
</x-ui::dropdown-menu>
