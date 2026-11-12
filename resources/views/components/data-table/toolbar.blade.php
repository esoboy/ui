@blaze

@props(['collapsible' => false, 'clearFilters' => false, 'clearAction' => 'resetFilters'])

@php($folds = $collapsible && isset($secondary) && $secondary->isNotEmpty())

<div {{ $attributes->merge(['data-slot' => 'data-table-toolbar', 'x-data' => $folds ? '{ filtersOpen: false }' : null]) }}>
    {{ $primary ?? '' }}
    @if ($folds)
        <x-ui::button variant="outline" size="sm" aria-expanded="false" x-bind:aria-expanded="filtersOpen" x-on:click="filtersOpen = ! filtersOpen">
            {{ __('ui::data-table.more_filters') }}
        </x-ui::button>
    @endif
    @if ($clearFilters)
        <x-ui::button variant="ghost" size="sm" data-data-table-reset :wire:click="$clearAction">
            {{ __('ui::data-table.clear_filters') }}
        </x-ui::button>
    @endif
    @isset($secondary)
        <div data-slot="data-table-secondary-filters" @if ($folds) x-show="filtersOpen" style="display: none" @endif>{{ $secondary }}</div>
    @endisset
</div>
