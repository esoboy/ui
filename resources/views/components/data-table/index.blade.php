@php
    $selectionModel = $dataTable->selectionModel();
    $selectedKeys = $dataTable->selectedKeys();
    $currentSelection = array_intersect_key($selectableKeys, $selectedKeys);
    $sort = $dataTable->boundValue($sortModel);
    $scope = $attributes->get('id') ?? $selectionModel ?? $layoutModel ?? $sortModel ?? 'table';
@endphp
<div {{ $attributes->whereDoesntStartWith('wire:model')->merge(['data-slot' => 'data-table']) }}
    x-data="{
        widths: {},
        measure(head) { this.widths[head.dataset.widthVar] = head.getBoundingClientRect().width + 'px' }
    }" x-bind:style="widths" data-selection-model="{{ $selectionModel }}">
    <div data-slot="data-table-container">
        @isset($toolbar)
            <div data-slot="data-table-toolbar">{{ $toolbar }}</div>
        @endisset

        <div data-slot="data-table-frame">
            @include('ui::partials.data-table.grid')
        </div>

        @if (isset($footer) || $paginator)
            <div data-slot="data-table-footer">
                @isset($footer)
                    {{ $footer }}
                @else
                    <x-ui::data-table.total />
                    <x-ui::pagination :paginator="$paginator" :max-pages="5" />
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
