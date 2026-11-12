@blaze

@props([
    'text' => null,
    'size' => 'default',
])

<x-ui::pagination.link :size="$size" :attributes="$attributes->merge(['aria-label' => __('ui::pagination.next_page')])">
    <span class="hidden sm:block">{{ $text ?? __('ui::pagination.next') }}</span>
    <x-ui::icon name="chevron-right" data-icon="inline-end" class="rtl:rotate-180" />
</x-ui::pagination.link>
