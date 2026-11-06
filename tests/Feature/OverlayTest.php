<?php

/*
 * Overlays carry no ids: ui.js pairs each trigger with the content after it, so a
 * re-render, or an overlay coming or going before another, changes nothing a morph
 * would match elements by.
 */

use Esoboy\Ui\Tests\Fixtures\Menus;
use Livewire\Livewire;

it('pairs every trigger with a unique PHP rendered target', function () {
    $html = Livewire::test(Menus::class)->html();
    $ids = idsOf($html, 'id');
    expect($ids)->not->toBeEmpty()->toHaveCount(count(array_unique($ids)));
    foreach (idsOf($html, 'commandfor') as $target) {
        expect($ids)->toContain($target);
    }
});

it('renders the same overlays when a component re-renders', function () {
    $component = Livewire::test(Menus::class);
    // What the component's root carries changes between requests; what is inside, not.
    $markup = fn () => preg_replace('/^<div[^>]*>/', '', $component->html());
    $first = $markup();

    $component->call('$refresh');

    expect($markup())->toBe($first);
});
