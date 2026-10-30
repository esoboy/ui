<?php

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
        ->toContain('<eso-image-preview data-slot="image-preview" class="flex gap-2">')
        ->toContain('<button type="button" data-slot="image-preview-trigger" data-src="/a-large.jpg" data-alt="A" aria-haspopup="dialog" class="size-20">')
        ->toContain('<img src="/a.jpg" alt="A" loading="lazy">')
        ->toContain('data-src="/b.jpg" data-alt="" aria-haspopup="dialog" aria-label="Preview image"')
        ->toContain('<dialog data-slot="image-preview-content" aria-label="Image preview" wire:ignore>')
        ->toContain('<img data-slot="image-preview-image" alt="" draggable="false">')
        ->toContain('<div role="toolbar" data-slot="image-preview-toolbar"');

    foreach (['close', 'previous', 'next', 'zoom-out', 'zoom-in', 'rotate-left', 'rotate-right', 'reset'] as $action) {
        expect($html)->toContain("data-image-preview=\"{$action}\"");
    }
});

it('shows custom thumbnail content instead of src', function () {
    $this->blade('<x-ui::image-preview><x-ui::image-preview.item src="/a.jpg"><span>Open</span></x-ui::image-preview.item></x-ui::image-preview>')
        ->assertSee('<span>Open</span>', false)
        ->assertDontSee('<img src="/a.jpg"', false);
});
