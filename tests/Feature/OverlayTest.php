<?php

/*
 * Overlays carry no ids: ui.js pairs each trigger with the content after it, so a
 * re-render, or an overlay coming or going before another, changes nothing a morph
 * would match elements by.
 */

use Esoboy\Ui\Tests\Fixtures\Menus;
use Livewire\Livewire;

it('renders triggers and contents without ids', function () {
    $html = Livewire::test(Menus::class)->html();

    expect($html)
        ->not->toMatch('/\sid="/')
        ->not->toContain('anchor-name')
        ->not->toContain('position-anchor')
        ->toContain('data-popover-trigger="data-popover-trigger" command="toggle-popover" commandfor=""')
        ->toContain('data-dropdown-menu-trigger="data-dropdown-menu-trigger" command="toggle-popover" commandfor="" aria-haspopup="menu"');
});

it('renders the same overlays when a component re-renders', function () {
    $component = Livewire::test(Menus::class);
    // What the component's root carries changes between requests; what is inside, not.
    $markup = fn () => preg_replace('/^<div[^>]*>/', '', $component->html());
    $first = $markup();

    $component->call('$refresh');

    expect($markup())->toBe($first);
});
