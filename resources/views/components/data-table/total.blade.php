@blaze

@props(['total'])

<p {{ $attributes->merge(['data-slot' => 'data-table-count', 'aria-live' => 'polite']) }}>{{ __('ui::data-table.total', ['total' => $total]) }}</p>
