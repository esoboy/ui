@php
    $owner = \Livewire\Livewire::current();
    $actions = $paginator instanceof \Illuminate\Contracts\Pagination\CursorPaginator ? ['setPage'] : ['gotoPage', 'previousPage', 'nextPage'];
    $live = $paginator && $owner && array_diff($actions, get_class_methods($owner)) === [];
@endphp
<nav {{ $attributes->merge(['data-size' => $paginationSize, 'data-paginated' => $paginator ? '' : null, 'class' => 'ui-pagination', 'aria-label' => __('ui::ui.pagination')]) }}>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @elseif ($paginator)
        @include('ui::partials.pagination-links')
    @endif

    @if ($jumpable && $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        @php
            $pageName = $paginator->getPageName();
            $url = $paginator->url(1);
            $jumpId = \Esoboy\Ui\Support\ControlAttributes::instanceName('jump');
            $action = $paginator->path().(parse_url($url, PHP_URL_FRAGMENT) !== null ? '#'.parse_url($url, PHP_URL_FRAGMENT) : '');
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            unset($query[$pageName]);
        @endphp
        <div class="ui-pagination-jump" data-page-name="{{ $pageName }}" data-page-url="{{ $url }}">
            <span aria-hidden="true">{{ __('ui::ui.pagination_jump_to') }}</span>
            @if ($owner)
                @teleport('body')
                    @include('ui::partials.pagination-jump-form')
                @endteleport
            @else
                @push('ui-forms')
                    @include('ui::partials.pagination-jump-form')
                @endpush
            @endif
            <x-ui::input-group :size="$paginationSize">
                <x-ui::input-group.input type="number" :id="$jumpId" :form="$jumpId.'-form'" :name="$pageName"
                    min="1" :max="$paginator->lastPage()" step="1" required :placeholder="$paginator->currentPage()"
                    :aria-label="__('ui::ui.pagination_page_number')" />
                <x-ui::input-group.addon align="inline-end">
                    <x-ui::input-group.button size="icon-xs" type="submit" :form="$jumpId.'-form'"
                        :aria-label="__('ui::ui.pagination_jump')" :title="__('ui::ui.pagination_jump')">
                        <x-ui::icon name="corner-down-left" />
                    </x-ui::input-group.button>
                </x-ui::input-group.addon>
            </x-ui::input-group>
        </div>
    @endif
</nav>
