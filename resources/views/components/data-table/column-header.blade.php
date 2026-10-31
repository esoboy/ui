@blaze

@use('Esoboy\Ui\Support\Model')
@use('Illuminate\Support\Js')

{{-- options (as Faceted Filter's) add a Filter submenu sharing WithDataTable's filters with the toolbar. --}}
@props([
    'column',
    'title',
    'sortable' => true,
    'hideable' => true,
    'options' => [],
])

@php
// Reads the sort from the Livewire component using WithDataTable, like TanStack's column state.
$table = Model::component();
$canSort = $sortable && $table && method_exists($table, 'sort');
$sorted = $canSort && $table->sortBy === $column ? $table->sortDirection : null;
$canHide = $hideable && $table && method_exists($table, 'toggleColumn');
$canFilter = $options && $table && method_exists($table, 'toggleFilter');
$chosen = $canFilter ? array_map('strval', $table->filters[$column] ?? []) : [];
@endphp

@if (! $canSort && ! $canFilter)
<div {{ $attributes }}>{{ $title }}</div>
@else
<div {{ $attributes->merge(['data-slot' => 'data-table-column-header']) }}>
    <x-ui::dropdown-menu>
        <x-ui::dropdown-menu.trigger variant="ghost" size="sm">
            <span>{{ $title }}</span>
            @if ($sorted === 'desc')
            <x-ui::icon name="arrow-down" />
            @elseif ($sorted === 'asc')
            <x-ui::icon name="arrow-up" />
            @elseif ($canSort)
            <x-ui::icon name="chevrons-up-down" />
            @else
            <x-ui::icon name="chevron-down" />
            @endif
        </x-ui::dropdown-menu.trigger>
        <x-ui::dropdown-menu.content align="start">
            @if ($canSort)
            <x-ui::dropdown-menu.item wire:click="sort('{{ $column }}', 'asc')"><x-ui::icon name="arrow-up" />{{ __('ui::data-table.asc') }}</x-ui::dropdown-menu.item>
            <x-ui::dropdown-menu.item wire:click="sort('{{ $column }}', 'desc')"><x-ui::icon name="arrow-down" />{{ __('ui::data-table.desc') }}</x-ui::dropdown-menu.item>
            @endif
            @if ($canFilter)
            @if ($canSort)
            <x-ui::dropdown-menu.separator />
            @endif
            <x-ui::dropdown-menu.sub>
                <x-ui::dropdown-menu.sub-trigger><x-ui::icon name="list-filter" />{{ __('ui::data-table.filter') }}</x-ui::dropdown-menu.sub-trigger>
                <x-ui::dropdown-menu.sub-content>
                    @foreach ($options as $value => $option)
                    <x-ui::dropdown-menu.checkbox-item :checked="in_array((string) $value, $chosen, true)" wire:click="toggleFilter({{ Js::from($column) }}, {{ Js::from((string) $value) }})">{{ is_array($option) ? ($option['label'] ?? $value) : $option }}</x-ui::dropdown-menu.checkbox-item>
                    @endforeach
                    @if ($chosen)
                    <x-ui::dropdown-menu.separator />
                    <x-ui::dropdown-menu.item wire:click="clearFilters({{ Js::from($column) }})">{{ __('ui::data-table.clear_filters') }}</x-ui::dropdown-menu.item>
                    @endif
                </x-ui::dropdown-menu.sub-content>
            </x-ui::dropdown-menu.sub>
            @endif
            @if ($canHide)
            <x-ui::dropdown-menu.separator />
            <x-ui::dropdown-menu.item wire:click="toggleColumn('{{ $column }}')"><x-ui::icon name="eye-off" />{{ __('ui::data-table.hide') }}</x-ui::dropdown-menu.item>
            @endif
        </x-ui::dropdown-menu.content>
    </x-ui::dropdown-menu>
</div>
@endif
