<?php

use Esoboy\Ui\Tests\Fixtures\Notifier;
use Livewire\Livewire;

/*
 * Tour: steps that point at elements of the page, started by a button or Ui::tour().
 */

it('mounts the spotlight and panel for a composed Blade tour', function () {
    $this->blade(<<<'BLADE'
        <x-ui::tour name="onboarding" :blocking="false">
            <x-ui::tour.step target="#search" title="Search">Find anything.</x-ui::tour.step>
        </x-ui::tour>
        BLADE)
        ->assertSeeMarkup('<eso-tour id="onboarding" data-slot="tour">')
        ->assertSeeMarkup('<div popover="manual" data-slot="tour-spotlight" aria-hidden="true">')
        ->assertDontSee('data-blocking', false)
        ->assertDontSee('<template data-tour-step', false);
});

it('renders the requested step and reports navigation and completion', function () {
    app()->setLocale('zh_CN');

    $tour = Livewire::test('ui.tour', ['steps' => [
        ['target' => '#search', 'title' => 'Search', 'description' => '<strong>Find anything.</strong>', 'side' => 'left', 'align' => 'start'],
        ['target' => '', 'title' => 'Done', 'description' => '', 'side' => 'bottom', 'align' => 'center'],
    ]])
        ->call('start', 2)
        ->assertSet('step', null)
        ->call('start', 0)
        ->assertDispatched('tour-change', step: 0)
        ->assertSee('<strong>Find anything.</strong>', false)
        ->assertSee('1 / 2')
        ->assertSee('下一步')
        ->call('next')
        ->assertSee('Done')
        ->assertSee('上一步')
        ->assertSee('完成')
        ->call('previous')
        ->assertSet('step', 0)
        ->call('finish')
        ->assertSet('step', 0);

    $tour->call('next')
        ->call('finish')
        ->assertDispatched('tour-close', step: 1, finished: true)
        ->assertSet('step', null);
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
