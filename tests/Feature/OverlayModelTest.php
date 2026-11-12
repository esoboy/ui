<?php

use Esoboy\Ui\Support\OverlayModel;
use Livewire\Component;
use Livewire\Livewire;

it('marks bound overlay roots with their binding', function (string $tag, string $marked) {
    preg_match('/uiModel="([^"]+)"/', $marked, $binding);
    expect(OverlayModel::mark($tag))->toContain('uiModel="'.$binding[1].'"');
})->with([
    'dialog' => ['<x-ui::dialog name="edit" wire:model="editing">', '<x-ui::dialog name="edit" wire:model="editing" uiModel="wire:model=editing">'],
    'modifiers' => ['<x-ui::sheet wire:model.live="open">', '<x-ui::sheet wire:model.live="open" uiModel="wire:model.live=open">'],
    'multiline' => ["<x-ui::popover\n    wire:model=\"shown\">", "<x-ui::popover\n    wire:model=\"shown\" uiModel=\"wire:model=shown\">"],
    'drawer' => ['<x-ui::drawer name="d" wire:model="form.open" />', '<x-ui::drawer name="d" wire:model="form.open" uiModel="wire:model=form.open" />'],
    'alert dialog' => ['<x-ui::alert-dialog name="a" wire:model="asking">', '<x-ui::alert-dialog name="a" wire:model="asking" uiModel="wire:model=asking">'],
]);

it('leaves other tags alone', function (string $tag) {
    expect(OverlayModel::mark($tag))->toBe($tag);
})->with([
    'content' => ['<x-ui::dialog.content wire:model="x">'],
    'another component' => ['<x-ui::input wire:model="name">'],
]);

it('binds the open state on the content, not on the root', function () {
    $component = new class extends Component
    {
        public bool $editing = false;

        public bool $shown = false;

        public function render(): string
        {
            return <<<'BLADE'
                <div>
                    <x-ui::dialog name="edit" wire:model.live="editing">
                        <x-ui::dialog.trigger><x-ui::button>Edit</x-ui::button></x-ui::dialog.trigger>
                        <x-ui::dialog.content>Form</x-ui::dialog.content>
                    </x-ui::dialog>
                    <x-ui::popover wire:model="shown">
                        <x-ui::popover.trigger><x-ui::button>Open</x-ui::button></x-ui::popover.trigger>
                        <x-ui::popover.content>Content</x-ui::popover.content>
                    </x-ui::popover>
                    <x-ui::sheet name="filters">
                        <x-ui::sheet.content>Filters</x-ui::sheet.content>
                    </x-ui::sheet>
                </div>
                BLADE;
        }
    };

    $html = Livewire::test($component::class)->html();

    expect($html)->toContain('uiDialog(')->toContain('&quot;property&quot;:&quot;editing&quot;')->toContain('&quot;property&quot;:&quot;shown&quot;')->toContainMarkup('<dialog id="edit" data-slot="dialog-content">')->not->toContain('<eso-dialog')->not->toContain('<eso-popover')->not->toContain('uiModel');
});

it('marks unbound roots to isolate their child context from an outer binding', function () {
    expect(OverlayModel::mark('<x-ui::dialog name="inner">'))->toContain('uiModel=""');
});

it('accepts single quotes and quoted greater-than signs in bindings', function () {
    expect(OverlayModel::mark("<x-ui::popover title='A > B' wire:model.live='form.open'>"))->toContain('uiModel="wire:model.live=form.open"')->toContain(':uiPopoverId=');
});
