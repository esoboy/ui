@blaze

<x-ui::button variant="ghost" size="icon-sm" :attributes="$attributes->merge(['data-sidebar' => 'trigger'])">
    <x-ui::icon name="panel-left" class="rtl:rotate-180" />
    <span class="sr-only">{{ __('ui::sidebar.toggle') }}</span>
</x-ui::button>
