@blaze

<span {{ $attributes->merge(['data-slot' => 'breadcrumb-ellipsis', 'role' => 'presentation', 'aria-hidden' => 'true']) }}>
    <x-ui::icon name="ellipsis" />
    <span class="sr-only">{{ __('ui::breadcrumb.more') }}</span>
</span>
