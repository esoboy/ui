@php
    $selectionModel = $dataTable->selectionModel();
    $selectedKeys = $dataTable->selectedKeys();
    $currentSelection = array_intersect_key($selectableKeys, $selectedKeys);
    $sort = $dataTable->boundValue($sortModel);
    $scope = $attributes->get('id') ?? $selectionModel ?? $layoutModel ?? $sortModel ?? 'table';
@endphp
<div {{ $attributes->whereDoesntStartWith('wire:model')->class('ui-data-table')->merge(['data-size' => $size]) }}
    @if ($selectionModel || array_column($layout->visible, 'widthVariable')) x-data="uiDataTable" x-bind:style="widths" @endif
    @if ($selectionModel) data-selection-model="{{ $selectionModel }}" @endif>
    <div class="ui-data-table-container">
        @isset($toolbar)
            <div class="ui-data-table-toolbar">{{ $toolbar }}</div>
        @endisset

        <div class="ui-data-table-frame">
            @include('ui::partials.data-table.grid')
        </div>

        @if (isset($footer) || $paginator)
            <div class="ui-data-table-footer">
                @isset($footer)
                    {{ $footer }}
                @else
                    <x-ui::data-table.total />
                    <x-ui::pagination :size="$size" :paginator="$paginator" :max-pages="5" />
                @endisset
            </div>
        @endif
    </div>

    @isset($selection)
        {{ $selection }}
    @elseif ($selectionModel && $selectedKeys)
        <x-ui::data-table.selection-toolbar />
    @endisset
</div>
