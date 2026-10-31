@blaze

@use('Esoboy\Ui\Support\Model')

{{-- shadcn's DataTablePagination for a Laravel paginator: selection count, rows per page (WithDataTable's perPageOptions()), page x of y, first / previous / next / last. --}}
@props([
    'paginator',
])

@php
$table = Model::component();
$selected = $table && property_exists($table, 'selected') ? count($table->selected) : 0;
$total = method_exists($paginator, 'total') ? $paginator->total() : $paginator->count();
$last = method_exists($paginator, 'lastPage') ? max($paginator->lastPage(), 1) : $paginator->currentPage() + ($paginator->hasMorePages() ? 1 : 0);
$page = $paginator->getPageName();
$sizes = $table && method_exists($table, 'perPageOptions') ? $table->perPageOptions() : null;
@endphp

<div {{ $attributes->merge(['data-slot' => 'data-table-pagination']) }}>
    <div data-slot="data-table-selection">{{ __('ui::data-table.selected', ['selected' => $selected, 'total' => $total]) }}</div>
    <div data-slot="data-table-pagination-controls">
        @if ($sizes !== null)
        <div data-slot="data-table-page-size">
            <p>{{ __('ui::data-table.rows_per_page') }}</p>
            <x-ui::select wire:model.live="perPage">
                <x-ui::select.trigger class="h-8 w-[70px]"><x-ui::select.value :placeholder="(string) $table->perPage" /></x-ui::select.trigger>
                <x-ui::select.content side="top">
                    @foreach ($sizes as $size)
                    <x-ui::select.item :value="(string) $size">{{ $size }}</x-ui::select.item>
                    @endforeach
                </x-ui::select.content>
            </x-ui::select>
        </div>
        @endif
        <div data-slot="data-table-page">{{ __('ui::data-table.page', ['current' => $paginator->currentPage(), 'total' => $last]) }}</div>
        <div data-slot="data-table-page-buttons">
            <x-ui::button variant="outline" size="icon" class="hidden size-8 lg:flex" wire:click="setPage(1, '{{ $page }}')" :disabled="$paginator->onFirstPage()">
                <span class="sr-only">{{ __('ui::data-table.first_page') }}</span>
                <x-ui::icon name="chevrons-left" />
            </x-ui::button>
            <x-ui::button variant="outline" size="icon" class="size-8" wire:click="previousPage('{{ $page }}')" :disabled="$paginator->onFirstPage()">
                <span class="sr-only">{{ __('ui::pagination.previous_page') }}</span>
                <x-ui::icon name="chevron-left" />
            </x-ui::button>
            <x-ui::button variant="outline" size="icon" class="size-8" wire:click="nextPage('{{ $page }}')" :disabled="! $paginator->hasMorePages()">
                <span class="sr-only">{{ __('ui::pagination.next_page') }}</span>
                <x-ui::icon name="chevron-right" />
            </x-ui::button>
            <x-ui::button variant="outline" size="icon" class="hidden size-8 lg:flex" wire:click="setPage({{ $last }}, '{{ $page }}')" :disabled="! $paginator->hasMorePages()">
                <span class="sr-only">{{ __('ui::data-table.last_page') }}</span>
                <x-ui::icon name="chevrons-right" />
            </x-ui::button>
        </div>
    </div>
</div>
