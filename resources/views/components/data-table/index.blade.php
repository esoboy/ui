@php
    $selectionModel = $dataTable->selectionModel();
    $selectedKeys = $dataTable->selectedKeys();
    $currentSelection = array_intersect_key($selectableKeys, $selectedKeys);
    $sort = $dataTable->boundValue($sortModel);
    $scope = $dataTable->scope();
    $pageValues = array_combine($selectableKeys, $dataTable->pageValues());
@endphp
<div {{ $attributes->whereDoesntStartWith('wire:model')->class('ui-data-table')->merge(['data-size' => $size]) }}
    @if ($selectionModel || array_column($layout->visible, 'widthVariable')) x-data="uiDataTable" x-bind:style="widths" @endif
    @if ($selectionModel) data-selection-model="{{ $selectionModel }}" @endif>
    @if ($selectionModel)
        <select hidden multiple data-table-selection {{ array_key_first($attributes->whereStartsWith('wire:model')->all()) }}="{{ $selectionModel }}">
            @foreach ($dataTable->selectionOptions() as $option)
                <option value="{{ $option['value'] }}" @if ($option['page']) data-page @endif @selected($option['selected'])>{{ $option['value'] }}</option>
            @endforeach
        </select>
    @endif
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
    @elseif ($selectionModel)
        <x-ui::data-table.selection-toolbar reactive-count />
    @endisset
</div>
