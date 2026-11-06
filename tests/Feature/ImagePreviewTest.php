<?php

use Livewire\Livewire;

it('renders the selected image, bounds navigation and handles an empty gallery', function () {
    $images = [['src' => '/a.jpg', 'alt' => 'Photo A'], ['src' => '/b.jpg', 'alt' => 'Photo B']];
    $viewer = Livewire::test('ui.image-preview', ['images' => $images])
        ->call('select', 1)
        ->assertSee(['src="/b.jpg"', 'alt="Photo B"'], false)
        ->assertSee('2 / 2')
        ->call('next')
        ->assertSet('index', 1)
        ->call('previous')
        ->assertSee(['src="/a.jpg"', 'alt="Photo A"'], false)
        ->assertSee('1 / 2');

    $viewer->call('select', -1)->assertSet('index', 0);
    Livewire::test('ui.image-preview')->call('select', 0)->assertSet('index', null);
});

/*
 * Image Preview: thumbnails and the viewer they open.
 */

it('renders thumbnails and one viewer for the group', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::image-preview class="flex gap-2">
            <x-ui::image-preview.item src="/a.jpg" preview-src="/a-large.jpg" alt="A" class="size-20" />
            <x-ui::image-preview.item src="/b.jpg" />
        </x-ui::image-preview>
        BLADE);

    expect($html)
        ->toContainMarkup('<eso-image-preview data-slot="image-preview" class="flex gap-2">')
        ->toContainMarkup('<button type="button" data-slot="image-preview-trigger" data-src="/a-large.jpg" data-alt="A" aria-haspopup="dialog" class="size-20">')
        ->toContainMarkup('<img src="/a.jpg" alt="A" loading="lazy">')
        ->toContain('data-src="/b.jpg" data-alt="" aria-haspopup="dialog" aria-label="Preview image"')
        ->toContainMarkup('<dialog data-slot="image-preview-content" aria-label="Image preview" wire:ignore.self>')
        ->toContainMarkup('<img data-slot="image-preview-image" alt="" draggable="false">')
        ->toContain('<div role="toolbar" data-slot="image-preview-toolbar"');

    foreach (['close', 'previous', 'next', 'zoom-out', 'zoom-in', 'rotate-left', 'rotate-right', 'reset'] as $action) {
        expect($html)->toContain("data-image-preview=\"{$action}\"");
    }
});

it('shows custom thumbnail content instead of src', function () {
    $this->blade('<x-ui::image-preview><x-ui::image-preview.item src="/a.jpg"><span>Open</span></x-ui::image-preview.item></x-ui::image-preview>')
        ->assertSeeMarkup('<span>Open</span>')
        ->assertDontSee('<img src="/a.jpg"', false);
});
