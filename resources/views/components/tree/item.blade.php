@blaze

@aware([
    'checkable' => false,
    'defaultExpanded' => [],
    'expandAll' => false,
])

{{-- An item: value and label (or a label slot), an optional icon slot, children as nested items or from item's children. A lazy item has children the tree's load method adds when it opens. wire:key keeps morphs matching items by value. --}}
@props([
    'value' => null,
    'label' => null,
    'item' => null,
    'disabled' => false,
    'lazy' => false,
])

@php
if ($item !== null) {
    $value = $item['value'];
    $label = $item['label'] ?? $item['value'];
    $disabled = $item['disabled'] ?? false;
    $lazy = $item['lazy'] ?? false;
}

$children = $item['children'] ?? [];
$lazy = $lazy && $children === [] && $slot->isEmpty();
$parent = $lazy || ($item !== null ? $children !== [] : $slot->isNotEmpty());
$expanded = $parent && ! $lazy && ($expandAll || in_array((string) $value, array_map('strval', (array) $defaultExpanded), true));
@endphp

<li {{ $attributes->merge([
    'role' => 'treeitem',
    'tabindex' => '-1',
    'wire:key' => 'tree-item-'.$value,
    'data-slot' => 'tree-item',
    'data-value' => $value,
    'data-lazy' => $lazy,
    'aria-expanded' => $parent ? ($expanded ? 'true' : 'false') : false,
    'aria-disabled' => $disabled ? 'true' : false,
]) }}>
    <div data-slot="tree-item-row">
        <span data-slot="tree-item-toggle" aria-hidden="true">@if ($parent)<x-ui::icon name="chevron-right" />@endif @if ($lazy)<x-ui::icon name="loader-circle" data-slot="tree-item-spinner" />@endif</span>
        @if ($checkable)
        <input type="checkbox" data-slot="checkbox" data-tree-check tabindex="-1" aria-hidden="true" @if ($disabled) disabled @endif>
        @endif
        @isset ($icon)
        <span data-slot="tree-item-icon">{{ $icon }}</span>
        @endisset
        <span data-slot="tree-item-label">{{ $label ?? '' }}</span>
    </div>
    @if ($parent)
    <ul role="group" data-slot="tree-group" @if (! $expanded) hidden @endif>
        @if ($item !== null)
        @foreach ($children as $child)
        <x-ui::tree.item :item="$child" />
        @endforeach
        @else
        {{ $slot }}
        @endif
    </ul>
    @endif
</li>
