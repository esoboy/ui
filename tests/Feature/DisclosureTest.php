<?php

/*
 * Accordion, Collapsible, Progress, Aspect Ratio, Scroll Area, Resizable, Carousel.
 */

use Esoboy\Ui\Tests\Fixtures\Disclosures;
use Livewire\Livewire;

describe('accordion', function () {
    it('renders items as details, opening the default ones', function () {
        $view = $this->blade(<<<'BLADE'
            <x-ui::accordion :value="['b']">
                <x-ui::accordion.item value="a">
                    <x-ui::accordion.trigger>A?</x-ui::accordion.trigger>
                    <x-ui::accordion.content class="pb-4">A.</x-ui::accordion.content>
                </x-ui::accordion.item>
                <x-ui::accordion.item value="b"><x-ui::accordion.trigger>B?</x-ui::accordion.trigger></x-ui::accordion.item>
            </x-ui::accordion>
            BLADE);

        $view->assertSeeMarkupInOrder([
            '<eso-accordion data-slot="accordion">',
            '<details data-slot="accordion-item" data-value="a" wire:ignore.self>',
            '<summary data-slot="accordion-trigger">',
            'A?',
            '<svg data-slot="accordion-trigger-icon" data-state="closed">',
            '<svg data-slot="accordion-trigger-icon" data-state="open">',
            '<div data-slot="accordion-content">',
            '<div class="pb-4">A.</div>',
            '<details data-slot="accordion-item" data-value="b" open="open" wire:ignore.self>',
        ]);
    });

    it('marks multiple accordions and disabled items', function () {
        $html = (string) $this->blade(<<<'BLADE'
            <x-ui::accordion multiple>
                <x-ui::accordion.item value="a" disabled><x-ui::accordion.trigger>A?</x-ui::accordion.trigger></x-ui::accordion.item>
            </x-ui::accordion>
            BLADE);

        expect($html)->toContainMarkup('<eso-accordion data-slot="accordion" data-multiple="data-multiple">')
            ->toContain('data-disabled="data-disabled"')
            ->toContainMarkup('<summary data-slot="accordion-trigger" aria-disabled="true" tabindex="-1">');
    });
});

describe('collapsible', function () {
    it('starts closed, or open by default', function () {
        $template = '<x-ui::collapsible :open="$open"><x-ui::collapsible.trigger>T</x-ui::collapsible.trigger><x-ui::collapsible.content>C</x-ui::collapsible.content></x-ui::collapsible>';

        $this->blade($template, ['open' => false])
            ->assertSeeMarkup('<eso-collapsible data-slot="collapsible">')
            ->assertSeeMarkup('<button type="button" data-slot="collapsible-trigger" aria-expanded="false">T</button>')
            ->assertSeeMarkup('<div data-slot="collapsible-content" wire:key="collapsible-content" hidden="hidden">C</div>');

        $this->blade($template, ['open' => true])
            ->assertSeeMarkup('<eso-collapsible data-slot="collapsible" data-open="data-open">')
            ->assertSee('aria-expanded="true" data-panel-open="data-panel-open"', false)
            ->assertSeeMarkup('<div data-slot="collapsible-content" wire:key="collapsible-content" data-open="data-open">C</div>');
    });

    it('starts from the bound Livewire value', function () {
        expect(Livewire::test(Disclosures::class)->html())
            ->toContainMarkup('<eso-collapsible data-slot="collapsible" data-open="data-open">');
    });
});

it('renders progress with its value', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::progress :value="30" :max="200">
            <x-ui::progress.label>Upload</x-ui::progress.label>
            <x-ui::progress.value />
        </x-ui::progress>
        BLADE);

    $view->assertSeeMarkupInOrder([
        '<div role="progressbar" aria-labelledby="ui-progress-label-1" aria-valuemin="0" aria-valuemax="200" aria-valuenow="30" aria-valuetext="15%" data-slot="progress" data-variant="default">',
        '<span id="ui-progress-label-1" data-slot="progress-label">Upload</span>',
        '<span data-slot="progress-value">15%</span>',
        '<div data-slot="progress-track">',
        '<div data-slot="progress-indicator"  style="inset-inline-start: 0; width: 15%" ></div>',
    ]);

    $this->blade('<x-ui::progress />')->assertSee('data-indeterminate', false)->assertDontSee('aria-valuenow', false)->assertDontSee('aria-labelledby', false);
    $this->blade('<x-ui::progress :value="100" />')->assertSee('data-complete', false);
});

it('names each progress by its own label, or by an own aria-labelledby', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::progress :value="10"><x-ui::progress.label>First</x-ui::progress.label></x-ui::progress>
        <x-ui::progress :value="20"><x-ui::progress.label id="second">Second</x-ui::progress.label></x-ui::progress>
        <x-ui::progress :value="30" aria-labelledby="heading" />
        BLADE);

    $view->assertSeeMarkupInOrder([
        'aria-labelledby="ui-progress-label-1"',
        '<span id="ui-progress-label-1" data-slot="progress-label">First</span>',
        'aria-labelledby="second"',
        '<span id="second" data-slot="progress-label">Second</span>',
        'aria-labelledby="heading"',
    ]);
});

it('renders a circle progress with its value in the middle', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::progress variant="circle" :value="75">
            <x-ui::progress.value />
        </x-ui::progress>
        BLADE);

    $view->assertSeeMarkupInOrder([
        'aria-valuetext="75%" data-slot="progress" data-variant="circle">',
        '<span data-slot="progress-value">75%</span>',
        '<svg data-slot="progress-circle">',
        '<circle data-slot="progress-circle-track" />',
        '<circle data-slot="progress-circle-indicator" pathLength="100"  style="stroke-dashoffset: 25"  />',
    ])->assertDontSee('progress-track"', false);

    $this->blade('<x-ui::progress variant="circle" />')
        ->assertSee('data-indeterminate', false)
        ->assertSeeMarkup('<circle data-slot="progress-circle-indicator" pathLength="100"  />');
});

it('renders an aspect ratio box', function () {
    $this->blade('<x-ui::aspect-ratio :ratio="16 / 9">x</x-ui::aspect-ratio>')
        ->assertSeeMarkup('<div data-slot="aspect-ratio" style="--ratio: 1.7777777777778;">x</div>');
});

it('renders a scroll area with both scrollbars, hidden until JS measures', function () {
    $view = $this->blade('<x-ui::scroll-area class="h-72">Tags</x-ui::scroll-area>');

    $view->assertSeeMarkupInOrder([
        '<eso-scroll-area data-slot="scroll-area" class="h-72">',
        '<div data-slot="scroll-area-viewport" tabindex="0">Tags</div>',
        '<div data-slot="scroll-area-scrollbar" data-orientation="vertical" hidden wire:ignore><div data-slot="scroll-area-thumb"></div></div>',
        '<div data-slot="scroll-area-scrollbar" data-orientation="horizontal" hidden wire:ignore>',
    ]);
});

it('renders resizable panels and handles', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::resizable.panel-group orientation="vertical">
            <x-ui::resizable.panel size="25" min-size="10">A</x-ui::resizable.panel>
            <x-ui::resizable.handle with-handle />
            <x-ui::resizable.panel>B</x-ui::resizable.panel>
        </x-ui::resizable.panel-group>
        BLADE);

    expect($html)
        ->toContainMarkup('<eso-resizable-panel-group data-slot="resizable-panel-group" aria-orientation="vertical" data-orientation="vertical">')
        ->toContainMarkup('<div data-slot="resizable-panel" data-initial-size="25" data-min-size="10" style="flex: 25 1 0px; overflow: hidden;" wire:ignore.self>A</div>')
        ->toContainMarkup('<div role="separator" tabindex="0" data-slot="resizable-handle" aria-orientation="horizontal" wire:ignore.self>')
        ->toContainMarkup('<div data-slot="resizable-panel" style="flex: 1 1 0px; overflow: hidden;" wire:ignore.self>B</div>');
});

it('renders a carousel with its controls', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::carousel align="start">
            <x-ui::carousel.content class="-ml-2"><x-ui::carousel.item class="basis-1/3">1</x-ui::carousel.item></x-ui::carousel.content>
            <x-ui::carousel.previous />
            <x-ui::carousel.next />
        </x-ui::carousel>
        BLADE);

    expect($html)
        ->toContainMarkup('<eso-carousel role="region" aria-roledescription="carousel" data-slot="carousel" data-orientation="horizontal" data-align="start">')
        ->toContainMarkup('<div data-slot="carousel-content" tabindex="0">')
        ->toContainMarkup('<div class="-ml-2"><div role="group" aria-roledescription="slide" data-slot="carousel-item" class="basis-1/3">1</div></div>')
        ->toMatch('/data-carousel="previous" disabled="disabled"/')
        ->toContainMarkup('<span class="sr-only">Previous slide</span>')
        ->toContainMarkup('<span class="sr-only">Next slide</span>');
});
