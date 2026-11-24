<x-ui::tooltip x-bind:data-disabled="String(Alpine.$data($el.closest('.ui-sidebar')).tooltipsDisabled)">
    <x-ui::tooltip.trigger>{{ $slot }}</x-ui::tooltip.trigger>
    <x-ui::tooltip.content side="right">{{ $tooltip }}</x-ui::tooltip.content>
</x-ui::tooltip>
