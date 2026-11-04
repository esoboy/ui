@blaze

@use('Esoboy\Ui\Support\Model')
@use('Illuminate\Support\Js')

{{-- shadcn's DataTableFacetedFilter over WithDataTable's filters. options: ['todo' => 'Todo'] or ['todo' => ['label' => 'Todo', 'icon' => 'circle']]; counts: facetCounts(). --}}
@props([
    'column',
    'title',
    'options' => [],
    'counts' => [],
])

@php
$table = Model::component();
$chosen = $table && property_exists($table, 'filters') ? array_map('strval', $table->filters[$column] ?? []) : [];
$options = collect($options)->mapWithKeys(fn ($option, $value) => [(string) $value => is_array($option) ? $option + ['label' => $value] : ['label' => $option]]);
@endphp

<x-ui::popover>
    <x-ui::popover.trigger variant="outline" size="sm" :attributes="$attributes->merge(['data-data-table-faceted-filter' => true])">
        <x-ui::icon name="circle-plus" />
        {{ $title }}
        @if ($chosen)
        <x-ui::separator orientation="vertical" />
        <x-ui::badge variant="secondary" data-data-table-filter-count>{{ count($chosen) }}</x-ui::badge>
        <span data-data-table-filter-values>
            @if (count($chosen) > 2)
            <x-ui::badge variant="secondary">{{ __('ui::data-table.selected_count', ['count' => count($chosen)]) }}</x-ui::badge>
            @else
            @foreach ($options as $value => $option)
            @if (in_array($value, $chosen, true))
            <x-ui::badge variant="secondary">{{ $option['label'] }}</x-ui::badge>
            @endif
            @endforeach
            @endif
        </span>
        @endif
    </x-ui::popover.trigger>
    <x-ui::popover.content align="start" data-data-table-filter-content>
        <x-ui::command>
            <x-ui::command.input :placeholder="$title" />
            <x-ui::command.list>
                <x-ui::command.empty>{{ __('ui::data-table.no_filter_results') }}</x-ui::command.empty>
                <x-ui::command.group>
                    @foreach ($options as $value => $option)
                    <x-ui::command.item :value="$value" :data-chosen="in_array($value, $chosen, true)" wire:click="toggleFilter({{ Js::from($column) }}, {{ Js::from($value) }})">
                        <span data-slot="data-table-filter-checkbox"><x-ui::icon name="check" /></span>
                        @if (isset($option['icon']))
                        <x-ui::icon :name="$option['icon']" />
                        @endif
                        <span>{{ $option['label'] }}</span>
                        @if (isset($counts[$value]))
                        <span data-slot="data-table-filter-count">{{ $counts[$value] }}</span>
                        @endif
                    </x-ui::command.item>
                    @endforeach
                </x-ui::command.group>
                @if ($chosen)
                <x-ui::command.separator />
                <x-ui::command.group>
                    <x-ui::command.item data-data-table-clear-filter wire:click="clearFilters({{ Js::from($column) }})">{{ __('ui::data-table.clear_filters') }}</x-ui::command.item>
                </x-ui::command.group>
                @endif
            </x-ui::command.list>
        </x-ui::command>
    </x-ui::popover.content>
</x-ui::popover>
