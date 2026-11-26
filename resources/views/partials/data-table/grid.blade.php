<x-ui::table :size="$size" :aria-label="$attributes->get('aria-label')" :aria-labelledby="$attributes->get('aria-labelledby')">
    <x-ui::table.header>
        <x-ui::table.row>
            @if ($selectionModel)
                @php($allSelected = $selectableKeys && count($currentSelection) === count($selectableKeys))
                <x-ui::table.head class="ui-table-selection-cell">
                    <x-ui::checkbox :size="$size" :aria-label="__('ui::ui.table_select_page')"
                        :checked="$allSelected" :disabled="! $selectableKeys"
                        :data-page-keys="json_encode($dataTable->pageValues(), JSON_THROW_ON_ERROR)"
                        x-effect="reflectSelection($el, $wire.$get($root.dataset.selectionModel))"
                        x-on:change="selectRows($el)" x-on:blur="forward('blur')" x-on:keydown.enter="forward('keydown')" />
                </x-ui::table.head>
            @endif
            @foreach ($layout->visible as $key => $column)
                @php($direction = $column['sortable'] && ($sort['column'] ?? null) === $key ? ($sort['direction'] ?? null) : null)
                <x-ui::table.head wire:key="{{ $scope }}-head-{{ $key }}" :data-column="$key" :data-align="$column['align']"
                    :data-fixed="$column['fixed']" :style="$column['fixed'] ? '--dt-inset:'.$column['inset'] : null"
                    :data-width-var="$column['widthVariable'] ?? null"
                    :x-resize="$column['fixed'] ? 'measure($el)' : null"
                    :aria-sort="match ($direction) { 'asc' => 'ascending', 'desc' => 'descending', default => null }">
                    @if ($sortModel && $column['sortable'])
                        <x-ui::dropdown-menu>
                            <x-ui::dropdown-menu.trigger>
                                <x-ui::button variant="ghost" size="sm" class="ui-table-sort-trigger">
                                    {{ $column['label'] }}
                                    <x-ui::icon :name="match ($direction) { 'asc' => 'arrow-up', 'desc' => 'arrow-down', default => 'chevrons-up-down' }" />
                                </x-ui::button>
                            </x-ui::dropdown-menu.trigger>
                            <x-ui::dropdown-menu.content align="start" class="ui-popup-centered" :aria-label="$column['label']">
                                @foreach (['default' => 'list', 'asc' => 'arrow-up-narrow-wide', 'desc' => 'arrow-down-wide-narrow'] as $option => $icon)
                                    <x-ui::dropdown-menu.item :aria-current="($direction ?? 'default') === $option ? 'true' : null"
                                        :wire:click="$dataTable->setValue($sortModel, $option === 'default' ? null : ['column' => $key, 'direction' => $option])">
                                        <x-ui::icon :name="$icon" />{{ __('ui::ui.table_sort_'.$option) }}
                                        @if (($direction ?? 'default') === $option)<x-ui::icon name="check" class="ui-table-sort-check" />@endif
                                    </x-ui::dropdown-menu.item>
                                @endforeach
                            </x-ui::dropdown-menu.content>
                        </x-ui::dropdown-menu>
                    @else
                        {{ $column['label'] }}
                    @endif
                </x-ui::table.head>
            @endforeach
        </x-ui::table.row>
    </x-ui::table.header>
    <x-ui::table.body>
        @forelse ($records as $id => $record)
            @php($selectedExpression = $selectionModel ? '($get('.\Illuminate\Support\Js::from($selectionModel).') ?? []).includes('.\Illuminate\Support\Js::from($pageValues[$id] ?? (string) $id).')' : null)
            <x-ui::table.row wire:key="{{ $scope }}-row-{{ $id }}" :data-state="isset($selectedKeys[$id]) ? 'selected' : null"
                :wire:bind:data-state="$selectedExpression ? $selectedExpression.' ? \'selected\' : null' : null">
                @if ($selectionModel)
                    <x-ui::table.cell class="ui-table-selection-cell">
                        <x-ui::checkbox :size="$size" :id="$scope.'-select-'.$id" data-ui-select-row :value="(string) $id"
                            :data-row-value="is_bool($pageValues[$id] ?? null) ? ($pageValues[$id] ? 'true' : 'false') : (string) ($pageValues[$id] ?? $id)"
                            :wire:bind:checked="$selectedExpression" x-on:change="selectRows($el)" x-on:blur="forward('blur')" x-on:keydown.enter="forward('keydown')"
                            :checked="isset($selectedKeys[$id])" :disabled="! isset($selectableKeys[$id])"
                            :aria-label="__('ui::ui.select_node', ['label' => $dataTable->rowLabel($record, $id)])" />
                    </x-ui::table.cell>
                @endif
                @foreach ($layout->visible as $key => $column)
                    <x-ui::table.cell wire:key="{{ $scope }}-cell-{{ $id }}-{{ $key }}" :data-column="$key" :data-align="$column['align']"
                        :data-fixed="$column['fixed']" :style="$column['fixed'] ? '--dt-inset:'.$column['inset'] : null">
                        {{ $dataTable->cell($id, $key) ?? ($column['field'] === null ? '' : (string) data_get($record, $column['field'])) }}
                    </x-ui::table.cell>
                @endforeach
            </x-ui::table.row>
        @empty
            <x-ui::table.empty :colspan="max(1, count($layout->visible) + (int) (bool) $selectionModel)">
                @isset($empty)
                    {{ $empty }}
                @else
                    @include('ui::partials.empty-state', ['emptyMessage' => 'table', 'emptySize' => 'default', 'emptyIcon' => 'inbox'])
                @endisset
            </x-ui::table.empty>
        @endforelse
    </x-ui::table.body>
</x-ui::table>
