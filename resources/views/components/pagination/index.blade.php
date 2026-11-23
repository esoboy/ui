@props(['color' => null, 'paginator' => null, 'maxPages' => null, 'jumpable' => false])
@php
    $attributes = \Esoboy\Ui\Support\ComponentColor::attributes($color, 'pagination', $attributes);
    $owner = \Livewire\Livewire::current();
    $actions = $paginator instanceof \Illuminate\Contracts\Pagination\CursorPaginator ? ['setPage'] : ['gotoPage', 'previousPage', 'nextPage'];
    $live = $paginator && $owner && array_diff($actions, get_class_methods($owner)) === [];
@endphp
<nav {{ $attributes->merge(['data-paginated' => $paginator ? '' : null, 'data-esoboy-ui' => '', 'data-slot' => 'pagination', 'aria-label' => __('ui::ui.pagination')]) }}>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @elseif ($paginator)
        @include('ui::partials.pagination-links')
    @endif

    @if ($jumpable && $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div data-esoboy-ui data-slot="pagination-jump" data-page-name="{{ $paginator->getPageName() }}"
            data-page-url="{{ $paginator->url(1) }}" x-data="{
                jump() {
                    const input = this.$refs.number
                    if (!input.reportValidity()) return
                    @if ($live)
                        this.$wire.gotoPage(input.valueAsNumber, this.$root.dataset.pageName)
                    @else
                        const url = new URL(this.$root.dataset.pageUrl, location.href)
                        url.searchParams.set(this.$root.dataset.pageName, input.value)
                        location.assign(url)
                    @endif
                }
            }">
            <span aria-hidden="true">{{ __('ui::ui.pagination_jump_to') }}</span>
            <x-ui::input-group>
                <x-ui::input-group.input type="number" x-ref="number" x-on:keydown.enter.stop.prevent="jump()" form="" name="pageNumber"
                    min="1" :max="$paginator->lastPage()" step="1" required :placeholder="$paginator->currentPage()"
                    :aria-label="__('ui::ui.pagination_page_number')" />
                <x-ui::input-group.addon align="inline-end">
                    <x-ui::input-group.button size="icon-xs" x-on:click="jump()" :aria-label="__('ui::ui.pagination_jump')" :title="__('ui::ui.pagination_jump')">
                        <x-ui::icon name="corner-down-left" />
                    </x-ui::input-group.button>
                </x-ui::input-group.addon>
            </x-ui::input-group>
        </div>
    @endif
</nav>
