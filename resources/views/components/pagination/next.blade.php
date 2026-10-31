@blaze

@props([
    'text' => null,
])

<x-ui::pagination.link size="default" :attributes="$attributes->merge(['aria-label' => __('ui::pagination.next_page')])">
    <span class="hidden sm:block">{{ $text ?? __('ui::pagination.next') }}</span>
    <x-ui::icon name="chevron-right" data-icon="inline-end" class="rtl:rotate-180" />
</x-ui::pagination.link>
