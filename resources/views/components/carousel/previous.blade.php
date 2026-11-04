@blaze

<x-ui::button variant="outline" size="icon-sm" :attributes="$attributes->merge(['data-carousel' => 'previous', 'disabled' => true])" wire:ignore.self>
    <x-ui::icon name="chevron-left" class="rtl:rotate-180" />
    <span class="sr-only">{{ __('ui::carousel.previous') }}</span>
</x-ui::button>
