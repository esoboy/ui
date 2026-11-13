@php($labelling = ['id', 'aria-label', 'aria-labelledby'])
<eso-tree-select {{ $attributes->whereDoesntStartWith('wire:model')->except(['name', 'class', 'style', ...$labelling])->merge(['data-slot' => 'tree-select', 'data-checkable' => (bool) ($settings['checkable'] ?? false), 'x-data' => 'uiTree', 'x-on:keydown' => 'key($event)', 'x-on:toggle.capture' => 'toggled($event)', 'x-on:ui-tree-picked' => 'close()', 'x-effect' => 'highlight($wire.query)']) }}>
    <x-ui::select.trigger :size="$settings['size'] ?? 'default'" data-tree-select-trigger aria-haspopup="tree" :data-placeholder="$labels === []" :disabled="$settings['disabled'] ?? false" :commandfor="$this->getId().'-tree-popup'" :attributes="$attributes->only(['class', 'style', ...$labelling])->merge($settings['validation'] ?? [])">
        <x-ui::select.value :placeholder="$labels ? implode(', ', $labels) : ($settings['placeholder'] ?? null)" />
    </x-ui::select.trigger>
    <div id="{{ $this->getId() }}-tree-popup" wire:key="{{ $this->getId() }}-tree-popup" popover="auto" data-slot="tree-select-content" data-side="bottom" data-align="start" style="--side-offset: 4px;" wire:ignore.self>
        <eso-tree data-slot="tree" @if ($settings['checkable'] ?? false) data-checkable @endif>
            @include('ui::livewire.tree-content', ['treeLabel' => new \Illuminate\View\ComponentAttributeBag])
        </eso-tree>
    </div>
</eso-tree-select>
