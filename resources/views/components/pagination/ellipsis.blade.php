@blaze

<span {{ $attributes->merge(['data-slot' => 'pagination-ellipsis', 'aria-hidden' => 'true']) }}>
    <x-ui::icon name="ellipsis" />
    <span class="sr-only">{{ __('ui::pagination.more') }}</span>
</span>
