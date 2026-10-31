@blaze

<x-ui::button variant="outline" size="icon-sm" :attributes="$attributes->merge(['data-carousel' => 'next'])" wire:ignore.self>
    <x-ui::icon name="chevron-right" class="rtl:rotate-180" />
    <span class="sr-only">{{ __('ui::carousel.next') }}</span>
</x-ui::button>
