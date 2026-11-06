<?php

use Esoboy\Ui\Support\Icons;

/*
 * Heading, Text, Badge, Avatar, Card, Kbd, Spinner, Skeleton, Empty, Result, Statistic, Item, Alert, Button Group.
 */

it('renders native heading levels independently of typography and preserves the slot', function (int $level) {
    $this->blade(
        '<x-ui::heading :level="$level" size="xs" color="primary" id="title"><em>Title</em></x-ui::heading>',
        ['level' => $level],
    )->assertSeeMarkup('<h'.$level.' id="title" data-slot="heading" data-size="xs" data-color="primary"><em>Title</em></h'.$level.'>');
})->with([1, 2, 3, 4, 5, 6]);

it('handles literal, omitted and invalid heading levels', function () {
    $this->blade(<<<'BLADE'
        <x-ui::heading level="3">Literal</x-ui::heading>
        <x-ui::heading>Default</x-ui::heading>
        <x-ui::heading level="7">Fallback</x-ui::heading>
        BLADE)
        ->assertSeeMarkup('<h3 data-slot="heading" data-size="default" data-color="default">Literal</h3>')
        ->assertSeeMarkup('<h2 data-slot="heading" data-size="default" data-color="default">Default</h2>')
        ->assertSeeMarkup('<h2 data-slot="heading" data-size="default" data-color="default">Fallback</h2>');
});

it('renders inline text with its typography and Blade slot', function () {
    $this->blade('<p><x-ui::text size="lg" color="muted" class="italic"><em>Note</em></x-ui::text></p>')
        ->assertSeeMarkupInOrder([
            '<p>',
            '<span class="italic" data-slot="text" data-size="lg" data-color="muted"><em>Note</em></span>',
            '</p>',
        ]);
});

it('renders badges as spans or links', function () {
    $this->blade('<x-ui::badge>New</x-ui::badge>')
        ->assertSeeMarkup('<span data-slot="badge" data-variant="default">New</span>');

    $this->blade('<x-ui::badge variant="outline" href="/inbox">Inbox</x-ui::badge>')
        ->assertSeeMarkup('<a data-slot="badge" data-variant="outline" href="/inbox">Inbox</a>');
});

it('renders count and dot badges, pinned by a wrapper', function () {
    $this->blade('<x-ui::badge :count="8" />')
        ->assertSeeMarkup('<span data-slot="badge" data-variant="default" data-count="data-count">8</span>');

    $this->blade('<x-ui::badge variant="destructive" :count="120" />')->assertSeeMarkup('>99+</span>');
    $this->blade('<x-ui::badge :count="1200" :max="999" />')->assertSeeMarkup('>999+</span>');
    $this->blade('<x-ui::badge :count="0" />')->assertDontSee('data-slot="badge"', false);
    $this->blade('<x-ui::badge :count="0" show-zero />')->assertSeeMarkup('>0</span>');

    $this->blade('<x-ui::badge.wrapper><x-ui::button size="icon">B</x-ui::button><x-ui::badge dot /></x-ui::badge.wrapper>')
        ->assertSeeMarkupInOrder(['<span data-slot="badge-wrapper">', '<button', '<span data-slot="badge" data-variant="default" data-dot="data-dot"></span>', '</span>']);
});

it('renders a result with default media, title and description for error pages', function () {
    $this->blade('<x-ui::result status="404" />')
        ->assertSeeMarkupInOrder([
            '<div data-slot="result" data-status="404">',
            '<div data-slot="result-header">',
            '<div data-slot="result-media" data-variant="code">404</div>',
            '<div data-slot="result-title">Page not found</div>',
            '<p data-slot="result-description">The page you&#039;re looking for doesn&#039;t exist or has been moved.</p>',
        ]);

    app()->setLocale('zh_CN');
    $this->blade('<x-ui::result :status="403" />')->assertSeeMarkup('<div data-slot="result-title">无权访问</div>');
    app()->setLocale('en');
});

it('renders a result with its own content and a status icon', function () {
    $this->blade(<<<'BLADE'
        <x-ui::result status="success">
            <x-ui::result.header>
                <x-ui::result.media />
                <x-ui::result.title>Paid</x-ui::result.title>
                <x-ui::result.description>Receipt sent.</x-ui::result.description>
            </x-ui::result.header>
            <x-ui::result.content>Actions</x-ui::result.content>
        </x-ui::result>
        BLADE)
        ->assertSeeMarkupInOrder([
            '<div data-slot="result" data-status="success">',
            '<div data-slot="result-media" data-variant="icon"><svg',
            Icons::markup('circle-check'),
            '<div data-slot="result-title">Paid</div>',
            '<p data-slot="result-description">Receipt sent.</p>',
            '<div data-slot="result-content">Actions</div>',
        ]);

    $this->blade('<x-ui::result status="error"><x-ui::result.media variant="default">img</x-ui::result.media></x-ui::result>')
        ->assertSeeMarkup('<div data-slot="result-media" data-variant="default">img</div>');
});

it('renders a statistic with a formatted value and a trend', function () {
    $this->blade(<<<'BLADE'
        <x-ui::statistic>
            <x-ui::statistic.label>Revenue</x-ui::statistic.label>
            <x-ui::statistic.value :value="1250.5" :precision="2" prefix="$" />
            <x-ui::statistic.trend :value="12.5" />
        </x-ui::statistic>
        BLADE)
        ->assertSeeMarkupInOrder([
            '<div data-slot="statistic">',
            '<div data-slot="statistic-label">Revenue</div>',
            '<div data-slot="statistic-value">$1,250.50</div>',
            '<span data-slot="badge" data-variant="outline" data-direction="up">',
            Icons::markup('trending-up'),
            '+12.5%',
        ]);

    $this->blade('<x-ui::statistic.trend :value="-3" variant="destructive" />')
        ->assertSeeMarkupInOrder(['data-variant="destructive" data-direction="down">', Icons::markup('trending-down'), '-3%']);
    $this->blade('<x-ui::statistic.trend :value="0">Flat</x-ui::statistic.trend>')
        ->assertSeeMarkupInOrder(['data-direction="flat">', Icons::markup('minus'), 'Flat']);
    $this->blade('<x-ui::statistic.value suffix="%">4.5</x-ui::statistic.value>')
        ->assertSeeMarkup('<div data-slot="statistic-value">4.5%</div>');
});

it('renders an avatar with its parts', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::avatar.group>
            <x-ui::avatar size="lg">
                <x-ui::avatar.image src="/me.png" alt="Me" />
                <x-ui::avatar.fallback>ME</x-ui::avatar.fallback>
                <x-ui::avatar.badge />
            </x-ui::avatar>
            <x-ui::avatar.group-count>+3</x-ui::avatar.group-count>
        </x-ui::avatar.group>
        BLADE);

    $view->assertSeeMarkup('<div data-slot="avatar-group">')
        ->assertSeeMarkup('<span data-slot="avatar" data-size="lg">')
        ->assertSeeMarkup('<img data-slot="avatar-image" src="/me.png" alt="Me">')
        ->assertSeeMarkup('<span data-slot="avatar-fallback">ME</span>')
        ->assertSeeMarkup('<span data-slot="avatar-badge"></span>')
        ->assertSeeMarkup('<div data-slot="avatar-group-count">+3</div>');
});

it('renders a card with its parts', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::card size="sm">
            <x-ui::card.header>
                <x-ui::card.title>Title</x-ui::card.title>
                <x-ui::card.description>Description</x-ui::card.description>
                <x-ui::card.action>Action</x-ui::card.action>
            </x-ui::card.header>
            <x-ui::card.content>Content</x-ui::card.content>
            <x-ui::card.footer>Footer</x-ui::card.footer>
        </x-ui::card>
        BLADE);

    $view->assertSeeMarkup('<div data-slot="card" data-size="sm">')
        ->assertSeeMarkupInOrder([
            '<div data-slot="card-header">',
            '<div data-slot="card-title">Title</div>',
            '<div data-slot="card-description">Description</div>',
            '<div data-slot="card-action">Action</div>',
            '<div data-slot="card-content">Content</div>',
            '<div data-slot="card-footer">Footer</div>',
        ]);
});

it('renders an alert', function () {
    $this->blade(<<<'BLADE'
        <x-ui::alert variant="destructive">
            <x-ui::alert.title>Failed</x-ui::alert.title>
            <x-ui::alert.description>Try again.</x-ui::alert.description>
            <x-ui::alert.action>Retry</x-ui::alert.action>
        </x-ui::alert>
        BLADE)
        ->assertSeeMarkup('<div role="alert" data-slot="alert" data-variant="destructive">')
        ->assertSeeMarkup('<div data-slot="alert-title">Failed</div>')
        ->assertSeeMarkup('<div data-slot="alert-description">Try again.</div>')
        ->assertSeeMarkup('<div data-slot="alert-action">Retry</div>');
});

it('renders kbd, skeleton and a translated spinner', function () {
    $this->blade('<x-ui::kbd.group><x-ui::kbd>⌘</x-ui::kbd><x-ui::kbd>K</x-ui::kbd></x-ui::kbd.group>')
        ->assertSeeMarkupInOrder(['<kbd data-slot="kbd-group">', '<kbd data-slot="kbd">⌘</kbd>', '<kbd data-slot="kbd">K</kbd>', '</kbd>']);

    $this->blade('<x-ui::skeleton class="h-4 w-32" />')
        ->assertSeeMarkup('<div data-slot="skeleton" class="h-4 w-32"></div>');

    app()->setLocale('zh_CN');

    $spinner = (string) $this->blade('<x-ui::spinner class="size-6" />');

    expect($spinner)
        ->toContain('role="status"')
        ->toContain('class="size-6" data-slot="spinner" aria-label="加载中"')
        ->toContainMarkup('<path d="M21 12a9 9 0 1 1-6.219-8.56"/>')
        ->not->toContain('aria-hidden');
});

it('renders an empty state', function () {
    $this->blade(<<<'BLADE'
        <x-ui::empty>
            <x-ui::empty.header>
                <x-ui::empty.media variant="icon">!</x-ui::empty.media>
                <x-ui::empty.title>Nothing</x-ui::empty.title>
                <x-ui::empty.description>Create one.</x-ui::empty.description>
            </x-ui::empty.header>
            <x-ui::empty.content>Actions</x-ui::empty.content>
        </x-ui::empty>
        BLADE)
        ->assertSeeMarkup('<div data-slot="empty">')
        ->assertSeeMarkup('<div data-slot="empty-icon" data-variant="icon">!</div>')
        ->assertSeeMarkup('<div data-slot="empty-title">Nothing</div>')
        ->assertSeeMarkup('<p data-slot="empty-description">Create one.</p>')
        ->assertSeeMarkup('<div data-slot="empty-content">Actions</div>');
});

it('renders items as divs or links', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::item.group>
            <x-ui::item variant="outline" size="sm">
                <x-ui::item.media variant="icon">i</x-ui::item.media>
                <x-ui::item.content>
                    <x-ui::item.title>Title</x-ui::item.title>
                    <x-ui::item.description>Description</x-ui::item.description>
                </x-ui::item.content>
                <x-ui::item.actions>Go</x-ui::item.actions>
            </x-ui::item>
            <x-ui::item.separator />
            <x-ui::item href="/profile">
                <x-ui::item.header>Header</x-ui::item.header>
                <x-ui::item.footer>Footer</x-ui::item.footer>
            </x-ui::item>
        </x-ui::item.group>
        BLADE);

    $view->assertSeeMarkup('<div role="list" data-slot="item-group">')
        ->assertSeeMarkup('<div data-slot="item" data-variant="outline" data-size="sm">')
        ->assertSeeMarkup('<div data-slot="item-media" data-variant="icon">i</div>')
        ->assertSeeMarkup('<div data-slot="item-title">Title</div>')
        ->assertSeeMarkup('<p data-slot="item-description">Description</p>')
        ->assertSeeMarkup('<div data-slot="item-actions">Go</div>')
        ->assertSeeMarkup('<div data-slot="item-separator" data-orientation="horizontal" role="none"></div>')
        ->assertSeeMarkup('<a data-slot="item" data-variant="default" data-size="default" href="/profile">')
        ->assertSeeMarkup('<div data-slot="item-header">Header</div>');
});

it('renders a button group', function () {
    $this->blade(<<<'BLADE'
        <x-ui::button-group orientation="vertical">
            <x-ui::button-group.text>https://</x-ui::button-group.text>
            <x-ui::button-group.separator />
            <x-ui::button variant="outline">Go</x-ui::button>
        </x-ui::button-group>
        BLADE)
        ->assertSeeMarkup('<div role="group" data-slot="button-group" data-orientation="vertical">')
        ->assertSeeMarkup('<div data-slot="button-group-text">https://</div>')
        ->assertSeeMarkup('<div data-slot="button-group-separator" data-orientation="vertical" role="none"></div>');
});
