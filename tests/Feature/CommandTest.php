<?php

/*
 * Command and Combobox.
 */

it('renders a command menu', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::command class="rounded-lg border" :should-filter="false">
            <x-ui::command.input placeholder="Search..." wire:model.live="q" />
            <x-ui::command.list>
                <x-ui::command.empty>No results.</x-ui::command.empty>
                <x-ui::command.group heading="Suggestions">
                    <x-ui::command.item value="cal" :keywords="['date', 'events']" wire:click="open">Calendar <x-ui::command.shortcut>⌘C</x-ui::command.shortcut></x-ui::command.item>
                    <x-ui::command.item disabled>Calculator</x-ui::command.item>
                </x-ui::command.group>
                <x-ui::command.separator />
            </x-ui::command.list>
        </x-ui::command>
        BLADE);

    $view->assertSee('<eso-command data-slot="command" data-should-filter="false" class="rounded-lg border">', false)
        ->assertSee('<input type="text" data-slot="command-input" autocomplete="off" spellcheck="false" role="combobox" aria-expanded="true" aria-autocomplete="list" placeholder="Search..." wire:model.live="q">', false)
        ->assertSee('<div role="listbox" aria-label="Suggestions" data-slot="command-list" wire:key="command-list">', false)
        ->assertSee('<div role="presentation" data-slot="command-empty">No results.</div>', false)
        ->assertSee('<div data-slot="command-group-heading" aria-hidden="true">Suggestions</div>', false)
        ->assertSee('<div role="option" wire:key="command-item-cal" data-slot="command-item" data-value="cal" data-keywords="date events" aria-selected="false" wire:click="open">', false)
        ->assertSee('<span data-slot="command-shortcut">⌘C</span>', false)
        ->assertSee('aria-selected="false" aria-disabled="true"', false)
        ->assertSee('<div role="none" data-slot="command-separator"></div>', false);
});

it('renders a command dialog with a translated, visually hidden title', function () {
    app()->setLocale('zh_CN');

    $this->blade('<x-ui::command.dialog name="palette"><x-ui::command.input /></x-ui::command.dialog>')
        ->assertSee('<dialog id="palette" data-slot="dialog-content"', false)
        ->assertSee('data-command-dialog', false)
        ->assertSee('<div data-slot="dialog-header" class="sr-only">', false)
        ->assertSee('>命令面板</h2>', false)
        ->assertSee('>搜索要执行的命令…</p>', false)
        ->assertSee('<eso-command data-slot="command" data-should-filter="true">', false)
        ->assertDontSee('data-variant="ghost" data-size="icon-sm"', false);
});

it('marks an autocomplete combobox, whose value is the typed text', function () {
    $this->blade('<x-ui::combobox autocomplete wire:model.live="email"><x-ui::combobox.input :show-trigger="false" /></x-ui::combobox>')
        ->assertSee('<eso-combobox data-slot="combobox" data-autocomplete="data-autocomplete" data-should-filter="true" wire:model.live="email">', false)
        ->assertDontSee('data-combobox-trigger', false);
});

it('renders a combobox with its input, trigger and clear', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::combobox wire:model="framework" default-value="next">
            <x-ui::combobox.input placeholder="Select a framework" show-clear />
            <x-ui::combobox.content>
                <x-ui::combobox.empty>No items found.</x-ui::combobox.empty>
                <x-ui::combobox.list>
                    <x-ui::combobox.group>
                        <x-ui::combobox.label>Frameworks</x-ui::combobox.label>
                        <x-ui::combobox.item value="next">Next.js</x-ui::combobox.item>
                    </x-ui::combobox.group>
                    <x-ui::combobox.separator />
                </x-ui::combobox.list>
            </x-ui::combobox.content>
        </x-ui::combobox>
        BLADE);

    expect($html)
        ->toContain('<eso-combobox data-slot="combobox" data-should-filter="true" data-value="&quot;next&quot;" wire:model="framework">')
        ->toContain('<div data-slot="input-group" role="group" data-combobox-anchor="data-combobox-anchor">')
        ->toContain('<input type="text" data-slot="input-group-control" data-combobox-input="data-combobox-input" role="combobox" autocomplete="off" aria-autocomplete="list" aria-expanded="false" placeholder="Select a framework">')
        ->toContain('data-combobox-trigger')
        ->toContain('data-combobox-clear')
        ->toContain('aria-label="Clear"')
        ->toContain('<div popover="manual" role="listbox" data-slot="combobox-content" data-side="bottom" data-align="start" style="--side-offset: 6px; --align-offset: 0px;">')
        ->toContain('<div role="option" wire:key="combobox-item-next" data-slot="combobox-item" data-value="next" aria-selected="false">')
        ->toContain('<div data-slot="combobox-label">Frameworks</div>');
});

it('renders a multiple combobox with a chip template', function () {
    app()->setLocale('zh_CN');

    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::combobox multiple :default-value="['a']" name="tags">
            <x-ui::combobox.chips class="max-w-xs">
                <x-ui::combobox.chips-input placeholder="Add" />
            </x-ui::combobox.chips>
            <x-ui::combobox.content />
        </x-ui::combobox>
        BLADE);

    expect($html)
        ->toContain('<eso-combobox data-slot="combobox" data-multiple="data-multiple" data-should-filter="true" data-value="[&quot;a&quot;]" data-name="tags">')
        ->toContain('<div data-slot="combobox-chips" data-combobox-anchor="data-combobox-anchor"')
        ->toContain('<template data-combobox-chip>')
        ->toContain('aria-label="移除"')
        ->toContain('<input type="text" data-slot="combobox-chip-input" data-combobox-input="data-combobox-input" role="combobox"');
});

it('renders a combobox with a trigger, its value and the search inside, as shadcn', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::combobox wire:model="country">
            <x-ui::combobox.trigger variant="outline" class="w-64 justify-between"><x-ui::combobox.value placeholder="Select a country" /></x-ui::combobox.trigger>
            <x-ui::combobox.content>
                <x-ui::combobox.input :show-trigger="false" placeholder="Search" />
                <x-ui::combobox.list><x-ui::combobox.item value="nl">Netherlands</x-ui::combobox.item></x-ui::combobox.list>
            </x-ui::combobox.content>
        </x-ui::combobox>
        <x-ui::combobox.chip>Tag</x-ui::combobox.chip>
        <x-ui::combobox.chip :show-remove="false">Fixed</x-ui::combobox.chip>
        <x-ui::combobox.clear />
        BLADE);

    expect($html)
        ->toMatch('/<button type="button" class="w-64 justify-between" data-slot="button" data-variant="outline" data-size="default" data-combobox-trigger="data-combobox-trigger" aria-haspopup="listbox" aria-expanded="false"><span data-slot="combobox-value" data-placeholder="Select a country">Select a country<\/span><svg[^>]*data-slot="combobox-trigger-icon"/')
        ->toMatch('/ data-slot="combobox-content"[^>]*>\s*<div data-slot="input-group"[^>]*>\s*<input type="text" data-slot="input-group-control" data-combobox-input/')
        ->toMatch('/<div data-slot="combobox-chip">\s*<span data-combobox-chip-label>Tag<\/span>\s*<button[^>]*data-combobox-chip-remove/')
        ->toMatch('/<span data-combobox-chip-label>Fixed<\/span>\s*<\/div>/')
        ->toMatch('/<button type="button" data-slot="button" data-variant="ghost" data-size="icon-xs" data-combobox-clear="data-combobox-clear" aria-label="Clear">/');
});

it('validates a combobox inside a field', function () {
    $this->withViewErrors(['framework' => 'Pick one.'])
        ->blade('<x-ui::field><x-ui::combobox id="framework" wire:model="framework"><x-ui::combobox.input /></x-ui::combobox></x-ui::field>')
        ->assertSee('aria-invalid="true" aria-describedby="framework-error"', false)
        ->assertSeeInOrder(['</eso-combobox>', 'Pick one.'], false);
});
