<?php

/*
 * Popover, Tooltip and Dropdown Menu: a trigger comes before its content, without ids;
 * ui.js pairs them and the browser anchors the content to the trigger.
 */

it('renders a popover trigger before its content', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::popover>
            <x-ui::popover.trigger><x-ui::button variant="outline">Open</x-ui::button></x-ui::popover.trigger>
            <x-ui::popover.content align="start" side-offset="8">
                <x-ui::popover.header>
                    <x-ui::popover.title>Dimensions</x-ui::popover.title>
                    <x-ui::popover.description>Set them.</x-ui::popover.description>
                </x-ui::popover.header>
            </x-ui::popover.content>
        </x-ui::popover>
        BLADE);

    expect($html)->toContainMarkup('<button type="button" data-slot="button" data-variant="outline" data-size="default" data-popover-trigger="data-popover-trigger" command="toggle-popover">Open</button>')
        ->and($html)->toContainMarkup('<div popover="auto" data-slot="popover-content" data-side="bottom" data-align="start" style="--side-offset: 8px; --align-offset: 0px;">')
        ->and($html)->toContainMarkup('<h2 data-slot="popover-title">Dimensions</h2>')
        ->and($html)->toContainMarkup('<p data-slot="popover-description">Set them.</p>');
});

it('gives nested popovers independent generated ids', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::popover>
            <x-ui::popover.trigger><x-ui::button>A</x-ui::button></x-ui::popover.trigger>
            <x-ui::popover.content>
                <x-ui::popover>
                    <x-ui::popover.trigger><x-ui::button>B</x-ui::button></x-ui::popover.trigger>
                    <x-ui::popover.content>b</x-ui::popover.content>
                </x-ui::popover>
            </x-ui::popover.content>
        </x-ui::popover>
        BLADE);

    expect(substr_count($html, 'data-popover-trigger="data-popover-trigger"'))->toBe(2)
        ->and(substr_count($html, 'command="toggle-popover"'))->toBe(2)
        ->and(substr_count($html, 'data-slot="popover-content"'))->toBe(2)
        ->and($html)->and(count(array_unique(idsOf($html, 'id'))))->toBe(2);
});

it('renders a tooltip shown on hover', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::tooltip>
            <x-ui::tooltip.trigger><x-ui::button variant="outline">Hover</x-ui::button></x-ui::tooltip.trigger>
            <x-ui::tooltip.content>Add to library</x-ui::tooltip.content>
        </x-ui::tooltip>
        BLADE);

    expect($html)->toContainMarkup('<button type="button" data-slot="button" data-variant="outline" data-size="default" data-tooltip-trigger="data-tooltip-trigger">Hover</button>')
        ->and($html)->toContainMarkup('<div popover="manual" role="tooltip" data-slot="tooltip-content" data-side="top" data-align="center" style="--side-offset: 4px; --align-offset: 0px;">Add to library</div>');
});

it('renders a dropdown menu whose items close the root menu', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::dropdown-menu>
            <x-ui::dropdown-menu.trigger><x-ui::button>Open</x-ui::button></x-ui::dropdown-menu.trigger>
            <x-ui::dropdown-menu.content align="start">
                <x-ui::dropdown-menu.label inset>Account</x-ui::dropdown-menu.label>
                <x-ui::dropdown-menu.group>
                    <x-ui::dropdown-menu.item wire:click="profile">Profile <x-ui::dropdown-menu.shortcut>⌘P</x-ui::dropdown-menu.shortcut></x-ui::dropdown-menu.item>
                    <x-ui::dropdown-menu.item href="/billing">Billing</x-ui::dropdown-menu.item>
                </x-ui::dropdown-menu.group>
                <x-ui::dropdown-menu.separator />
                <x-ui::dropdown-menu.sub>
                    <x-ui::dropdown-menu.sub-trigger>Invite</x-ui::dropdown-menu.sub-trigger>
                    <x-ui::dropdown-menu.sub-content>
                        <x-ui::dropdown-menu.item>Email</x-ui::dropdown-menu.item>
                    </x-ui::dropdown-menu.sub-content>
                </x-ui::dropdown-menu.sub>
                <x-ui::dropdown-menu.item variant="destructive">Delete</x-ui::dropdown-menu.item>
            </x-ui::dropdown-menu.content>
        </x-ui::dropdown-menu>
        BLADE);

    expect($html)->toContainMarkup('<button type="button" data-slot="button" data-variant="default" data-size="default" data-dropdown-menu-trigger="data-dropdown-menu-trigger" command="toggle-popover" aria-haspopup="menu">Open</button>')
        ->and($html)->toContainMarkup('<div popover="auto" role="menu" tabindex="-1" data-slot="dropdown-menu-content" data-side="bottom" data-align="start" style="--side-offset: 4px; --align-offset: 0px;">')
        ->and($html)->toContainMarkup('<div data-slot="dropdown-menu-label" data-inset="data-inset">Account</div>')
        ->and($html)->toContainMarkup('<button type="button" role="menuitem" data-slot="dropdown-menu-item" data-variant="default" wire:click="profile">')
        ->and($html)->toContainMarkup('<span data-slot="dropdown-menu-shortcut">⌘P</span>')
        ->and($html)->toContainMarkup('<a role="menuitem" data-slot="dropdown-menu-item" data-variant="default" href="/billing">Billing</a>')
        ->and($html)->toContainMarkup('<div role="separator" data-slot="dropdown-menu-separator"></div>')
        ->and($html)->toContainMarkup('<button aria-haspopup="menu" data-slot="dropdown-menu-sub-trigger" command="toggle-popover">')
        ->and($html)->toContainMarkup('<div popover="auto" role="menu" tabindex="-1" data-slot="dropdown-menu-sub-content" data-side="right" data-align="start" style="--side-offset: 0px; --align-offset: -3px;">')
        ->and($html)->toContain('data-variant="destructive"')
        // ui.js closes the root menu when an item is chosen.
        ->and($html)->not->toContain('hide-popover');
});

it('binds checkbox and radio items with wire:model', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::dropdown-menu.checkbox-item wire:model.live="statusBar" class="w-full">Status Bar</x-ui::dropdown-menu.checkbox-item>
        <x-ui::dropdown-menu.radio-group wire:model.live="position">
            <x-ui::dropdown-menu.radio-item value="top">Top</x-ui::dropdown-menu.radio-item>
        </x-ui::dropdown-menu.radio-group>
        BLADE);

    expect($html)->toContainMarkup('<label data-slot="dropdown-menu-checkbox-item" class="w-full">')
        ->and($html)->toContainMarkup('<input type="checkbox" role="menuitemcheckbox" wire:model.live="statusBar">')
        ->and($html)->toContainMarkup('<div role="group" data-slot="dropdown-menu-radio-group">')
        ->and($html)->toContainMarkup('<input type="radio" role="menuitemradio" value="top">');
});

it('keeps a style given to a component after its own', function () {
    $this->blade('<x-ui::popover><x-ui::popover.trigger><x-ui::button style="color: red">Open</x-ui::button></x-ui::popover.trigger><x-ui::popover.content style="width: 10rem">x</x-ui::popover.content></x-ui::popover>')
        ->assertSeeMarkupInOrder(['style="color: red;"', '--align-offset: 0px; width: 10rem;"']);

    $this->blade('<x-ui::sidebar.provider style="--sidebar-width: 20rem">x</x-ui::sidebar.provider>')
        ->assertSee('style="--sidebar-width: 16rem; --sidebar-width-icon: 3rem; --sidebar-width: 20rem;"', false);
});

it('shifts content along its edge by alignOffset', function () {
    $this->blade('<x-ui::popover><x-ui::popover.trigger><x-ui::button>Open</x-ui::button></x-ui::popover.trigger><x-ui::popover.content align="end" :align-offset="-6">x</x-ui::popover.content></x-ui::popover>')
        ->assertSee('data-align="end" style="--side-offset: 4px; --align-offset: -6px;"', false);
});

it('binds multiple overlay behaviors to the supplied trigger control', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::dropdown-menu>
            <x-ui::dropdown-menu.trigger><x-ui::sidebar.menu-button size="lg">Me</x-ui::sidebar.menu-button></x-ui::dropdown-menu.trigger>
            <x-ui::dropdown-menu.content>m</x-ui::dropdown-menu.content>
        </x-ui::dropdown-menu>
        <x-ui::dropdown-menu>
            <x-ui::tooltip>
                <x-ui::tooltip.trigger><x-ui::dropdown-menu.trigger><button type="button" id="my-menu-trigger">More</button></x-ui::dropdown-menu.trigger></x-ui::tooltip.trigger>
                <x-ui::tooltip.content>More actions</x-ui::tooltip.content>
            </x-ui::tooltip>
            <x-ui::dropdown-menu.content>m</x-ui::dropdown-menu.content>
        </x-ui::dropdown-menu>
        BLADE);

    expect($html)
        ->toContainMarkup('<button type="button" data-slot="sidebar-menu-button" data-sidebar="menu-button" data-variant="default" data-size="lg" data-dropdown-menu-trigger="data-dropdown-menu-trigger" command="toggle-popover" aria-haspopup="menu">Me</button>')
        ->toContainMarkup('<button type="button" id="my-menu-trigger" data-dropdown-menu-trigger="data-dropdown-menu-trigger" command="toggle-popover" aria-haspopup="menu" data-tooltip-trigger="data-tooltip-trigger">More</button>');
});
