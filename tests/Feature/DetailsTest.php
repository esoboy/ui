<?php

/*
 * Description List, Steps, Timeline.
 */

it('renders a description list as dl with groups', function () {
    $this->blade(<<<'BLADE'
        <x-ui::description-list orientation="horizontal" :columns="2">
            <x-ui::description-list.item :span="2">
                <x-ui::description-list.term>Order</x-ui::description-list.term>
                <x-ui::description-list.details>#4189</x-ui::description-list.details>
            </x-ui::description-list.item>
        </x-ui::description-list>
        BLADE)->assertSeeInOrder([
        '<dl data-slot="description-list" data-orientation="horizontal" style="--columns: 2;">',
        '<div data-slot="description-list-item" style="--span: 2;">',
        '<dt data-slot="description-list-term">Order</dt>',
        '<dd data-slot="description-list-details">#4189</dd>',
    ], false);
});

it('numbers steps and derives their status from the current one', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::steps :current="2">
            <x-ui::steps.item title="Account" description="Sign up" />
            <x-ui::steps.item title="Shop" />
            <x-ui::steps.item title="Done" />
        </x-ui::steps>
        <x-ui::steps :current="1" orientation="vertical">
            <x-ui::steps.item title="Placed" status="error" />
            <x-ui::steps.item title="Shipped" />
        </x-ui::steps>
        BLADE);

    preg_match_all('/data-status="(\w+)"/', $html, $statuses);
    preg_match_all('/data-slot="steps-indicator">\s*(\d)/', $html, $numbers);

    expect($statuses[1])->toBe(['complete', 'current', 'upcoming', 'error', 'upcoming'])
        ->and($numbers[1])->toBe(['2', '3', '2'])
        ->and($html)->toContain('<ol data-slot="steps" data-orientation="vertical">')
        ->toContain('data-status="current" aria-current="step"')
        ->toContain('<span data-slot="steps-title">Account</span>')
        ->toContain('<span data-slot="steps-description">Sign up</span>')
        ->toContain('<path d="M20 6 9 17l-5-5"/>')
        ->toContain('<path d="M18 6 6 18"/>');
});

it('renders timeline items with their variant and icon', function () {
    $this->blade(<<<'BLADE'
        <x-ui::timeline>
            <x-ui::timeline.item variant="success">
                <x-slot:icon>✓</x-slot:icon>
                <x-ui::timeline.header>
                    <x-ui::timeline.title>Delivered</x-ui::timeline.title>
                    <x-ui::timeline.time datetime="2026-10-27">Today</x-ui::timeline.time>
                </x-ui::timeline.header>
                <x-ui::timeline.description>Signed for.</x-ui::timeline.description>
            </x-ui::timeline.item>
            <x-ui::timeline.item>Plain</x-ui::timeline.item>
        </x-ui::timeline>
        BLADE)->assertSeeInOrder([
        '<ol data-slot="timeline">',
        '<li data-slot="timeline-item" data-variant="success">',
        '<span data-slot="timeline-indicator" aria-hidden="true">✓</span>',
        '<div data-slot="timeline-title">Delivered</div>',
        '<time data-slot="timeline-time" datetime="2026-10-27">Today</time>',
        '<div data-slot="timeline-description">Signed for.</div>',
        '<li data-slot="timeline-item" data-variant="default">',
        '<span data-slot="timeline-indicator" aria-hidden="true"></span>',
    ], false);
});
