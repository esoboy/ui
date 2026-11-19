@props(['collapsible' => false, 'initiallyCollapsed' => true, 'clearAction' => null])
@php($canCollapse = $collapsible && isset($secondary) && $secondary->isNotEmpty())
<div {{ $attributes->merge(['data-slot' => 'data-table-filters']) }}>
    <x-ui::collapsible :open="! $canCollapse || ! $initiallyCollapsed">
        @isset($primary)
            <div data-slot="data-table-filter-primary">{{ $primary }}</div>
        @endisset
        @if ($canCollapse || $clearAction)
            <div data-slot="data-table-filter-actions">
                @if ($canCollapse)
                    <x-ui::collapsible.trigger>
                        <x-ui::button variant="outline" size="sm"><x-ui::icon name="list-filter" />{{ __('ui::ui.table_more_filters') }}</x-ui::button>
                    </x-ui::collapsible.trigger>
                @endif
                @if ($clearAction)
                    <x-ui::button variant="ghost" size="sm" wire:click="{{ $clearAction }}">{{ __('ui::ui.table_clear_filters') }}<x-ui::icon name="x" /></x-ui::button>
                @endif
            </div>
        @endif
        @isset($secondary)
            @if ($canCollapse)
                <x-ui::collapsible.content class="ui-table-secondary"><div class="ui-table-filter-fields">{{ $secondary }}</div></x-ui::collapsible.content>
            @else
                <div class="ui-table-secondary ui-table-filter-fields">{{ $secondary }}</div>
            @endif
        @endisset
    </x-ui::collapsible>
</div>
