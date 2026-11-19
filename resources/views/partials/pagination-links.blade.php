@php
    $cursor = $paginator instanceof \Illuminate\Contracts\Pagination\CursorPaginator;
    $lengthAware = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $name = \Illuminate\Support\Js::from($cursor ? $paginator->getCursorName() : $paginator->getPageName());
    $live = (bool) \Livewire\Livewire::current();
    $previous = $cursor ? 'setPage('.\Illuminate\Support\Js::from($paginator->previousCursor()?->encode()).', '.$name.')' : 'previousPage('.$name.')';
    $next = $cursor ? 'setPage('.\Illuminate\Support\Js::from($paginator->nextCursor()?->encode()).', '.$name.')' : 'nextPage('.$name.')';
    $pages = [];
    if ($lengthAware && $maxPages !== null) {
        $size = min(max(1, (int) $maxPages), $paginator->lastPage());
        $start = max(1, min($paginator->currentPage() - intdiv($size, 2), $paginator->lastPage() - $size + 1));
        $pages = range($start, $start + $size - 1);
    } elseif ($lengthAware) {
        $pages = $paginator->linkCollection()->slice(1, -1)->pluck('page');
    } elseif (! $cursor) {
        $pages = [$paginator->currentPage()];
    }
@endphp
<x-ui::pagination.content>
    <x-ui::pagination.item>
        <x-ui::pagination.button size="default" class="ui-pagination-previous" :aria-label="__('ui::ui.previous_page')"
            :link="$live ? null : $paginator->previousPageUrl()" :wire:click="$live ? $previous : null" :disabled="$paginator->onFirstPage()">
            <x-ui::icon name="chevron-left" data-icon="inline-start" />
            <span class="ui-pagination-label">{{ __('ui::ui.previous_page') }}</span>
        </x-ui::pagination.button>
    </x-ui::pagination.item>
    @foreach ($pages as $number)
        <x-ui::pagination.item>
            @if ($number === null)
                <x-ui::pagination.ellipsis />
            @else
                <x-ui::pagination.button :active="$number === $paginator->currentPage()" :link="$live ? null : $paginator->url($number)"
                    :wire:click="$live ? 'gotoPage('.$number.', '.$name.')' : null">{{ $number }}</x-ui::pagination.button>
            @endif
        </x-ui::pagination.item>
    @endforeach
    <x-ui::pagination.item>
        <x-ui::pagination.button size="default" class="ui-pagination-next" :aria-label="__('ui::ui.next_page')"
            :link="$live ? null : $paginator->nextPageUrl()" :wire:click="$live ? $next : null" :disabled="! $paginator->hasMorePages()">
            <span class="ui-pagination-label">{{ __('ui::ui.next_page') }}</span>
            <x-ui::icon name="chevron-right" data-icon="inline-end" />
        </x-ui::pagination.button>
    </x-ui::pagination.item>
</x-ui::pagination.content>
