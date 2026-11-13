@php($labelling = ['aria-label', 'aria-labelledby'])
<eso-tree {{ $attributes->whereDoesntStartWith('wire:model')->except(['name', ...$labelling])->merge(['data-slot' => 'tree', 'data-checkable' => (bool) ($settings['checkable'] ?? false), 'data-draggable' => (bool) ($settings['draggable'] ?? false), 'x-data' => 'uiTree', 'x-on:keydown' => 'key($event)', 'x-on:pointerdown' => 'press($event)', 'x-on:pointermove.window' => 'move($event)', 'x-on:pointerup.window' => 'end(true)', 'x-on:pointercancel.window' => 'end(false)', 'x-on:click.capture' => 'suppress($event)', 'x-effect' => 'highlight($wire.query)']) }}>
    @include('ui::livewire.tree-content', ['treeLabel' => $attributes->only($labelling)])
</eso-tree>
