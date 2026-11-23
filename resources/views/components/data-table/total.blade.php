@props(['value' => null])
@aware(['dataTable' => null])
@php
    $source = $value ?? ($dataTable?->paginator ?? $dataTable?->records);
    $total = match (true) {
        $source instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator => $source->total(),
        $source instanceof \Illuminate\Contracts\Pagination\Paginator, $source instanceof \Illuminate\Contracts\Pagination\CursorPaginator => null,
        is_int($source) && $source >= 0 => $source,
        is_countable($source) => count($source),
        default => null,
    };
@endphp
@if ($total !== null)
    <span {{ $attributes->class('ui-data-table-total') }}>{{ trans_choice('ui::ui.table_total', $total, ['count' => $total]) }}</span>
@endif
