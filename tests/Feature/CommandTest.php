<?php

use Livewire\Livewire;

it('filters a command query and renders its groups on the server', function () {
    $markup = '<div data-slot="command-group"><div data-slot="command-item" data-keywords="偏好">设置</div></div>'
        .'<div data-slot="command-group"><div data-slot="command-item">账单</div></div>';

    $component = Livewire::test('ui.command', ['markup' => $markup])->set('query', '偏好');

    expect($component->html())
        ->toContainMarkup('<div data-slot="command-item" data-keywords="偏好">设置</div>')
        ->toContainMarkup('<div data-slot="command-item" hidden>账单</div>');
});

/*
 * Command and Combobox.
 */

it('renders a command menu', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::command class="rounded-lg border" :filter="false">
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

    $view->assertSeeMarkup('<eso-command data-slot="command" data-filter="false" class="rounded-lg border">')
        ->assertSeeMarkup('<input type="text" data-slot="input-group-control" class="p-0 text-sm" data-command-input="data-command-input" autocomplete="off" spellcheck="false" role="combobox" aria-expanded="true" aria-autocomplete="list" placeholder="Search..." wire:model.live="q">')
        ->assertSeeMarkup('<div role="listbox" aria-label="Suggestions" data-slot="command-list" wire:key="command-list">')
        ->assertSeeMarkup('<div role="presentation" data-slot="command-empty">No results.</div>')
        ->assertSeeMarkup('<div data-slot="command-group-heading" aria-hidden="true">Suggestions</div>')
        ->assertSeeMarkup('<div role="option" wire:key="command-item-cal" data-slot="command-item" data-value="cal" data-keywords="date events" aria-selected="false" wire:click="open">')
        ->assertSeeMarkup('<span data-slot="command-shortcut">⌘C</span>')
        ->assertSee('aria-selected="false" aria-disabled="true"', false)
        ->assertSeeMarkup('<div role="none" data-slot="command-separator"></div>');
});

it('renders a command dialog with a translated, visually hidden title', function () {
    app()->setLocale('zh_CN');

    $this->blade('<x-ui::command.dialog name="palette"><x-ui::command.input /></x-ui::command.dialog>')
        ->assertSeeMarkup('<dialog id="palette" data-slot="dialog-content">')
        ->assertSee('data-command-dialog', false)
        ->assertSeeMarkup('<div data-slot="dialog-header" class="sr-only">')
        ->assertSeeMarkup('>命令面板</h2>')
        ->assertSeeMarkup('>搜索要执行的命令…</p>')
        ->assertSeeMarkup('<eso-command data-slot="command" data-filter="true">')
        ->assertDontSee('data-variant="ghost" data-size="icon-sm"', false);
});

it('renders a free text combobox without a trigger when requested', function () {
    $this->blade('<x-ui::combobox autocomplete :trigger="false" value="hi" :options="[\'hi@example.com\']" />')->assertSee('data-autocomplete', false)->assertSee('value="hi"', false)->assertDontSee('data-combobox-trigger', false);
});

it('renders a combobox from options with a PHP selected label', function () {
    $html = (string) $this->blade('<x-ui::combobox value="next" :options="[\'next\' => \'Next.js\', \'nuxt\' => \'Nuxt\']" placeholder="Select a framework" clearable empty="No items found." />');
    expect($html)->toContainMarkup('<input data-combobox-input value="Next.js" placeholder="Select a framework">')->toContain('data-combobox-trigger')->toContain('data-combobox-clear')->toContainMarkup('<div role="option" data-value="next" aria-selected="true">')->toContain('No items found.');
});

it('associates the label with the combobox control rendered from options', function (string $mode, string $control) {
    $html = (string) $this->blade(<<<BLADE
        <label id="framework-label" for="framework">Framework</label>
        <x-ui::combobox {$mode} id="framework" aria-label="Framework chooser" aria-labelledby="framework-label" :options="[]" />
        BLADE);

    expect($html)->toContainMarkup("<{$control} id=\"framework\" aria-label=\"Framework chooser\" aria-labelledby=\"framework-label\">")
        ->and(substr_count($html, ' id="framework"'))->toBe(1)
        ->and(substr_count($html, ' aria-label="Framework chooser"'))->toBe(1)
        ->and(substr_count($html, ' aria-labelledby="framework-label"'))->toBe(1);
})->with([
    'single' => ['', 'input role="combobox"'],
    'autocomplete' => ['autocomplete', 'input role="combobox"'],
    'multiple' => ['multiple', 'input role="combobox" data-slot="combobox-chip-input"'],
    'button' => ['button', 'button data-combobox-trigger'],
]);

it('renders multiple chosen chips and native form values in PHP', function () {
    app()->setLocale('zh_CN');
    $html = (string) $this->blade('<x-ui::combobox multiple :value="[\'a\']" :options="[\'a\' => \'Alpha\']" name="tags" placeholder="Add" class="max-w-xs" />');
    expect($html)->toContainMarkup('<div data-slot="combobox-chips" class="max-w-xs">')->toContainMarkup('<span data-combobox-chip-label>Alpha</span>')->toContain('aria-label="移除"')->toContainMarkup('<input type="hidden" name="tags[]" value="a">')->not->toContain('<template');
});

it('renders a button combobox with its search inside the popover', function () {
    $html = (string) $this->blade('<x-ui::combobox button :options="[\'nl\' => \'Netherlands\']" placeholder="Select a country" class="w-64 justify-between" />');
    expect($html)->toContainMarkup('<button class="w-64 justify-between" data-combobox-trigger aria-haspopup="listbox">')->toContainMarkup('<span data-slot="combobox-value" data-placeholder="Select a country">Select a country</span>')->toContain('data-combobox-input')->toContain('Netherlands');
});

it('validates a combobox inside a field', function () {
    $this->withViewErrors(['framework' => 'Pick one.'])
        ->blade('<x-ui::field><x-ui::combobox id="framework" wire:model="framework" /></x-ui::field>')
        ->assertSee('aria-invalid="true" aria-describedby="framework-error"', false)
        ->assertSeeMarkupInOrder(['</eso-combobox>', 'Pick one.']);
});
