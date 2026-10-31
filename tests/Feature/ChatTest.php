<?php

/*
 * Message, Bubble, Marker, Attachment, Message Scroller.
 */

it('renders a message with its parts', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::message.group>
            <x-ui::message align="end">
                <x-ui::message.avatar>AB</x-ui::message.avatar>
                <x-ui::message.content>
                    <x-ui::message.header>Mia</x-ui::message.header>
                    <x-ui::message.footer>Delivered</x-ui::message.footer>
                </x-ui::message.content>
            </x-ui::message>
        </x-ui::message.group>
        BLADE);

    $view->assertSeeInOrder([
        '<div data-slot="message-group">',
        '<div data-slot="message" data-align="end">',
        '<div data-slot="message-avatar">AB</div>',
        '<div data-slot="message-content">',
        '<div data-slot="message-header">Mia</div>',
        '<div data-slot="message-footer">Delivered</div>',
    ], false);
});

it('renders bubbles whose content can be a link or a button', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::bubble.group>
            <x-ui::bubble variant="tinted" align="end">
                <x-ui::bubble.content>Hi</x-ui::bubble.content>
                <x-ui::bubble.reactions side="top" align="start">👍</x-ui::bubble.reactions>
            </x-ui::bubble>
            <x-ui::bubble><x-ui::bubble.content href="/order">Order</x-ui::bubble.content></x-ui::bubble>
            <x-ui::bubble variant="outline"><x-ui::bubble.content type="button" wire:click="reply">Refund</x-ui::bubble.content></x-ui::bubble>
        </x-ui::bubble.group>
        BLADE);

    $view->assertSeeInOrder([
        '<div data-slot="bubble-group">',
        '<div data-slot="bubble" data-variant="tinted" data-align="end">',
        '<div data-slot="bubble-content">Hi</div>',
        '<div data-slot="bubble-reactions" data-align="start" data-side="top">👍</div>',
        '<div data-slot="bubble" data-variant="default" data-align="start"><a data-slot="bubble-content" href="/order">Order</a></div>',
        '<button data-slot="bubble-content" type="button" wire:click="reply">Refund</button>',
    ], false);
});

it('renders markers, as a button with type', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::marker variant="separator">
            <x-ui::marker.icon>i</x-ui::marker.icon>
            <x-ui::marker.content>Today</x-ui::marker.content>
        </x-ui::marker>
        <x-ui::marker type="button" wire:click="loadEarlier">Load earlier</x-ui::marker>
        BLADE);

    $view->assertSeeInOrder([
        '<div data-slot="marker" data-variant="separator">',
        '<span data-slot="marker-icon" aria-hidden="true">i</span>',
        '<span data-slot="marker-content">Today</span>',
        '<button data-slot="marker" data-variant="default" type="button" wire:click="loadEarlier">Load earlier</button>',
    ], false);
});

it('renders an attachment with its parts', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::attachment.group>
            <x-ui::attachment state="uploading" size="sm" orientation="vertical">
                <x-ui::attachment.media variant="image"><img src="/a.jpg" alt=""></x-ui::attachment.media>
                <x-ui::attachment.content>
                    <x-ui::attachment.title>a.jpg</x-ui::attachment.title>
                    <x-ui::attachment.description>48%</x-ui::attachment.description>
                </x-ui::attachment.content>
                <x-ui::attachment.actions><x-ui::attachment.action aria-label="Remove">x</x-ui::attachment.action></x-ui::attachment.actions>
                <x-ui::attachment.trigger href="/a.jpg" />
            </x-ui::attachment>
        </x-ui::attachment.group>
        BLADE);

    $view->assertSeeInOrder([
        '<div data-slot="attachment-group">',
        '<div data-slot="attachment" data-state="uploading" data-size="sm" data-orientation="vertical">',
        '<div data-slot="attachment-media" data-variant="image"><img src="/a.jpg" alt=""></div>',
        '<span data-slot="attachment-title">a.jpg</span>',
        '<span data-slot="attachment-description">48%</span>',
        '<div data-slot="attachment-actions">',
        'data-variant="ghost" data-size="icon-xs" data-attachment-action="data-attachment-action" aria-label="Remove">x</button>',
        '<a data-slot="attachment-trigger" href="/a.jpg"></a>',
    ], false);

    $this->blade('<x-ui::attachment.trigger aria-label="Open" />')
        ->assertSee('<button type="button" data-slot="attachment-trigger" aria-label="Open"></button>', false);
});

describe('message scroller', function () {
    it('renders the frame with the provider options, hidden until positioned', function () {
        $view = $this->blade(<<<'BLADE'
            <x-ui::message-scroller.provider auto-scroll default-scroll-position="last-anchor" :scroll-previous-item-peek="32">
                <x-ui::message-scroller class="flex-1">
                    <x-ui::message-scroller.viewport>
                        <x-ui::message-scroller.content>
                            <x-ui::message-scroller.item message-id="m1" scroll-anchor wire:key="m1">Hi</x-ui::message-scroller.item>
                            <x-ui::message-scroller.item>Typing</x-ui::message-scroller.item>
                        </x-ui::message-scroller.content>
                    </x-ui::message-scroller.viewport>
                    <x-ui::message-scroller.button />
                </x-ui::message-scroller>
            </x-ui::message-scroller.provider>
            BLADE);

        $view->assertSeeInOrder([
            '<eso-message-scroller data-slot="message-scroller" data-auto-scroll="data-auto-scroll" data-default-scroll-position="last-anchor" data-scroll-edge-threshold="8" data-scroll-previous-item-peek="32" data-scroll-margin="0" data-pending-scroll="data-pending-scroll" class="flex-1" wire:ignore.self>',
            '<div role="region" aria-label="Messages" tabindex="0" data-slot="message-scroller-viewport" data-pending-scroll="data-pending-scroll" wire:ignore.self>',
            '<div role="log" aria-relevant="additions" data-slot="message-scroller-content">',
            '<div data-slot="message-scroller-item" data-message-id="m1" data-scroll-anchor="true" wire:key="m1">Hi</div>',
            '<div data-slot="message-scroller-item" data-scroll-anchor="false">Typing</div>',
            '<div aria-hidden="true" data-message-scroller-spacer hidden wire:ignore></div>',
            'data-variant="secondary" data-size="icon-sm"',
            'data-message-scroller-button="end" data-direction="end" data-behavior="smooth" data-active="false" inert="inert" tabindex="-1"',
            '<span class="sr-only">Scroll to end</span>',
        ], false);
    });

    it('opens visibly at the start, and can stop preserving on prepend', function () {
        $html = (string) $this->blade(<<<'BLADE'
            <x-ui::message-scroller.provider default-scroll-position="start">
                <x-ui::message-scroller>
                    <x-ui::message-scroller.viewport :preserve-scroll-on-prepend="false" />
                    <x-ui::message-scroller.button direction="start" />
                </x-ui::message-scroller>
            </x-ui::message-scroller.provider>
            BLADE);

        expect($html)->not->toContain('data-pending-scroll')
            ->toContain('data-preserve-scroll-on-prepend="false"')
            ->toContain('<span class="sr-only">Scroll to start</span>');
    });
});
