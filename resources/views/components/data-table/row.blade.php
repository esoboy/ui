@blaze

@props([
    'id',
    'parent' => null,
    'level' => null,
    'expanded' => false,
])

{{-- A Table Row the Data Table can expand: its Row Detail, or with parent its child rows (tree data, level indenting them), which start hidden. wire:key keeps morphs matching rows by id. --}}
<x-ui::table.row :attributes="$attributes->merge([
    'wire:key' => 'data-table-row-'.$id,
    'data-row-id' => $id,
    'data-parent' => $parent,
    'data-default-expanded' => (bool) $expanded,
    'hidden' => $parent !== null,
    'style' => $level ? '--level: '.$level.';' : null,
])">{{ $slot }}</x-ui::table.row>
