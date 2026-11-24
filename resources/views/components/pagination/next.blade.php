@props(['size' => null, 'text' => null])
@aware(['paginationSize' => 'default'])
<x-ui::pagination.link :size="$size ?? $paginationSize" :aria-label="__('ui::ui.next_page')" {{ $attributes->class('ui-pagination-next') }}><span class="ui-pagination-label">{{ $text ?? __('ui::ui.next_page') }}</span><x-ui::icon name="chevron-right" data-icon="inline-end" /></x-ui::pagination.link>
