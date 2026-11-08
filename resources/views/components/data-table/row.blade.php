{{-- Read the named cell slots from Laravel's slot collection. --}}
@aware(['tableLayout'])
@props(['entry', 'selectable' => true, 'expanded' => null])

@php($hasDetail = $tableLayout['details'] && isset($detail) && $detail->isNotEmpty())

<x-ui::table.row :attributes="$attributes->merge([
    'wire:key' => 'data-table-row-'.$entry->id,
    'data-row-id' => $entry->id,
    'data-parent' => $entry->parent,
    'data-expanded' => $entry->hasChildren && $expanded !== null ? ($expanded ? 'true' : 'false') : null,
    'data-state' => in_array($entry->id, $tableLayout['selected'], true) ? 'selected' : null,
    'hidden' => $entry->parent !== null,
])">
    @if ($tableLayout['selectable'])
        <x-ui::table.cell sticky="start"><x-ui::data-table.select-row :value="$entry->id" :disabled="! $selectable" /></x-ui::table.cell>
    @endif
    @if ($tableLayout['details'])
        <x-ui::table.cell class="px-0">
            @if ($hasDetail)
                <x-ui::data-table.row-toggle kind="detail" />
            @endif
        </x-ui::table.cell>
    @endif
    @foreach ($tableLayout['columns'] as $key => $column)
        @php($cell = in_array($key, ['actions', 'detail'], true) ? null : ($__laravel_slots[$key] ?? null))
        <x-ui::table.cell :sticky="$column['fixed'] ?? null">
            @if ($loop->first && $tableLayout['tree'])
                <div class="flex items-center gap-1" style="padding-inline-start: {{ $entry->depth }}rem">
                    @if ($entry->hasChildren)
                        <x-ui::data-table.row-toggle kind="tree" />
                    @else
                        <span class="size-6 shrink-0" aria-hidden="true"></span>
                    @endif
                    {{ $cell ?? data_get($entry->row, $key) }}
                </div>
            @else
                {{ $cell ?? data_get($entry->row, $key) }}
            @endif
        </x-ui::table.cell>
    @endforeach
    @if ($tableLayout['actions'])
        <x-ui::table.cell>{{ $actions ?? '' }}</x-ui::table.cell>
    @endif
</x-ui::table.row>

@if ($hasDetail)
    <x-ui::data-table.row-detail :for="$entry->id" :colspan="$tableLayout['span']">{{ $detail }}</x-ui::data-table.row-detail>
@endif
