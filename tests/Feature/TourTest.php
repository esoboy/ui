<?php

use Esoboy\Ui\Tests\Fixtures\Notifier;
use Livewire\Livewire;

/*
 * Tour: steps that point at elements of the page, started by a button or Ui::tour().
 */

it('renders the steps as templates beside a spotlight and a panel', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::tour name="onboarding">
            <x-ui::tour.step target="#search" title="Search">Find anything.</x-ui::tour.step>
            <x-ui::tour.step target="#profile" side="left" align="start">Your account.</x-ui::tour.step>
            <x-ui::tour.step title="All set">That is all.</x-ui::tour.step>
        </x-ui::tour>
        BLADE);

    expect($html)
        ->toContain('<eso-tour id="onboarding" data-slot="tour">')
        ->toContain('<template data-tour-step="data-tour-step" data-target="#search" data-side="bottom" data-align="center">')
        ->toContain('<h2 data-slot="tour-title">Search</h2>')
        ->toContain('<div data-slot="tour-description">Find anything.</div>')
        ->toContain('<template data-tour-step="data-tour-step" data-target="#profile" data-side="left" data-align="start">')
        ->toContain('<template data-tour-step="data-tour-step" data-side="bottom" data-align="center">')
        ->toContain('<div popover="manual" data-slot="tour-spotlight" aria-hidden="true" wire:ignore  data-blocking ></div>')
        ->toContain('<div popover="manual" role="dialog" aria-modal="false" data-slot="tour-content" wire:ignore>')
        ->toContain('data-variant="ghost" data-size="icon-sm" data-tour-action="close" aria-label="Close"><svg')
        ->toContain('<span data-slot="tour-counter" data-format=":current / :total"></span>')
        ->toContain('data-variant="outline" data-size="sm" data-tour-action="previous">Previous</button>')
        ->toContain('data-variant="default" data-size="sm" data-tour-action="next">Next</button>')
        ->toContain('data-variant="default" data-size="sm" data-tour-action="finish">Finish</button>')
        ->and(substr_count($html, 'data-slot="tour-title"'))->toBe(2);
});

it('can leave the page clickable around the target', function () {
    $this->blade('<x-ui::tour name="t" :blocking="false"><x-ui::tour.step>x</x-ui::tour.step></x-ui::tour>')
        ->assertSee('<div popover="manual" data-slot="tour-spotlight" aria-hidden="true" wire:ignore ></div>', false)
        ->assertDontSee('data-blocking', false);
});

it('translates its buttons and counter', function () {
    app()->setLocale('zh_CN');

    $this->blade('<x-ui::tour name="t"><x-ui::tour.step>x</x-ui::tour.step></x-ui::tour>')
        ->assertSee('data-format=":current / :total"', false)
        ->assertSee('aria-label="关闭"', false)
        ->assertSeeInOrder(['上一步', '下一步', '完成']);
});

it('starts and closes a tour by name', function () {
    Livewire::test(Notifier::class)
        ->call('welcome')
        ->assertDispatched('ui-tour', name: 'onboarding', open: true, step: 0)
        ->call('resume')
        ->assertDispatched('ui-tour', name: 'onboarding', open: true, step: 2)
        ->call('skip')
        ->assertDispatched('ui-tour', name: 'onboarding', open: false, step: null);
});
