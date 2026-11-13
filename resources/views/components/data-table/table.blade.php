{{-- This view is rendered by the class component that provides entries() to the caller. --}}
@use('Esoboy\Ui\Support\Model')
@use('Illuminate\Support\Js')

@php($owner = Model::component())

<x-ui::data-table.content :sticky-header="$stickyHeader" :attributes="$attributes">
    <x-ui::table.header>
        <x-ui::table.row>
            @if ($tableLayout['selectable'])
                <x-ui::table.head scope="col" class="w-8" sticky="start"><x-ui::data-table.select-all /></x-ui::table.head>
            @endif
            @if ($details)
                <x-ui::table.head scope="col" class="w-8"><span class="sr-only">{{ __('ui::data-table.expand_detail') }}</span></x-ui::table.head>
            @endif
            @foreach ($tableLayout['columns'] as $key => $column)
                @php($sorted = $owner && data_get($owner, 'sortBy') === $key ? data_get($owner, 'sortDirection') : null)
                <x-ui::table.head scope="col" :sticky="$column['fixed'] ?? null" :aria-sort="match ($sorted) {'asc' => 'ascending', 'desc' => 'descending', default => null}">
                    @if (($column['sortable'] ?? false) && $owner && method_exists($owner, 'sort'))
                        <x-ui::dropdown-menu>
                            <x-ui::dropdown-menu.trigger>
                                <x-ui::button variant="ghost" size="sm" class="px-2" data-data-table-sort :aria-label="$column['title'].' — '.__('ui::data-table.sort')">
                                    {{ $column['title'] }}
                                    <x-ui::icon :name="match ($sorted) {'asc' => 'arrow-up', 'desc' => 'arrow-down', default => 'chevrons-up-down'}" data-icon="inline-end" />
                                </x-ui::button>
                            </x-ui::dropdown-menu.trigger>
                            <x-ui::dropdown-menu.content align="start">
                                <x-ui::dropdown-menu.group>
                                    @foreach (['default' => __('ui::data-table.default_sort'), 'asc' => __('ui::data-table.asc'), 'desc' => __('ui::data-table.desc')] as $direction => $label)
                                        @php($active = $direction === ($sorted ?? 'default'))
                                        <x-ui::dropdown-menu.item
                                            role="menuitemradio"
                                            :aria-checked="$active ? 'true' : 'false'"
                                            :data-data-table-sort-option="$direction"
                                            :wire:click="'sort('.Js::from($key).', '.Js::from($direction).')'"
                                        >
                                            <span class="size-4 shrink-0" aria-hidden="true">
                                                @if ($active)
                                                    <x-ui::icon name="check" />
                                                @endif
                                            </span>
                                            {{ $label }}
                                        </x-ui::dropdown-menu.item>
                                    @endforeach
                                </x-ui::dropdown-menu.group>
                            </x-ui::dropdown-menu.content>
                        </x-ui::dropdown-menu>
                    @else
                        {{ $column['title'] }}
                    @endif
                </x-ui::table.head>
            @endforeach
            @if ($actions)
                <x-ui::table.head scope="col">{{ $actionsTitle }}</x-ui::table.head>
            @endif
        </x-ui::table.row>
    </x-ui::table.header>

    <x-ui::table.body>
        @if ($slot->isEmpty())
            <x-ui::data-table.empty :colspan="$tableLayout['span']" />
        @else
            {{ $slot }}
        @endif
    </x-ui::table.body>
    @isset($summary)
        <x-ui::table.footer>{{ $summary }}</x-ui::table.footer>
    @endisset
</x-ui::data-table.content>
