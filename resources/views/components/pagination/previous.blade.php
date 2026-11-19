@props(['text' => null])
<x-ui::pagination.link size="default" :aria-label="__('ui::ui.previous_page')" {{ $attributes->class('ui-pagination-previous') }}><x-ui::icon name="chevron-left" data-icon="inline-start" /><span class="ui-pagination-label">{{ $text ?? __('ui::ui.previous_page') }}</span></x-ui::pagination.link>
