@blaze

@props([
    'text' => null,
])

<x-ui::pagination.link size="default" :attributes="$attributes->merge(['aria-label' => __('ui::pagination.previous_page')])">
    <x-ui::icon name="chevron-left" data-icon="inline-start" class="rtl:rotate-180" />
    <span class="hidden sm:block">{{ $text ?? __('ui::pagination.previous') }}</span>
</x-ui::pagination.link>
