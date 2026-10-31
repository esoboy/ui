<?php

/*
 * Cropper: Origin UI's; the script sizes the crop area and moves the image under it.
 */

it('renders the cropper with its description, image and crop area', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::cropper image="/photo.jpg" :aspect-ratio="16 / 9" class="h-80" wire:model="crop">
            <x-ui::cropper.image class="opacity-90" />
            <x-ui::cropper.crop-area class="rounded-full" />
        </x-ui::cropper>
        BLADE);

    expect($html)
        ->toContain('<eso-cropper data-slot="cropper" role="application" tabindex="0" aria-label="Interactive image cropper" aria-description="Use mouse wheel or pinch gesture to zoom. Drag with mouse or touch, or use arrow keys to pan the image within the crop area." data-image="/photo.jpg" data-aspect-ratio="1.7777777777778" data-crop-padding="25" data-min-zoom="1" data-max-zoom="3" data-zoom-sensitivity="0.005" data-keyboard-step="10" class="h-80" wire:model="crop">')
        ->toContain('<div data-slot="cropper-image-wrapper" wire:ignore>')
        ->toContain('<img data-slot="cropper-image" src="/photo.jpg" alt="" draggable="false" aria-hidden="true" class="opacity-90">')
        ->toContain('<div data-slot="cropper-crop-area" aria-hidden="true" class="rounded-full" hidden wire:ignore></div>');
});

it('takes the zoom options', function () {
    $this->blade('<x-ui::cropper id="avatar" image="/a.jpg" :min-zoom="0.5" :max-zoom="5" :zoom="2" :crop-padding="10" :keyboard-step="20" />')
        ->assertSee('data-image="/a.jpg" data-aspect-ratio="1" data-crop-padding="10" data-min-zoom="0.5" data-max-zoom="5" data-zoom-sensitivity="0.005" data-keyboard-step="20" data-zoom="2" id="avatar"', false);
});

it('hands the script a crop it is given, and only the crop', function () {
    $this->blade('<x-ui::cropper image="/a.jpg" :default-value="[\'x\' => \'12\', \'y\' => 0, \'width\' => 300, \'height\' => 300, \'extra\' => 1]" />')
        ->assertSee('data-value="{&quot;x&quot;:12,&quot;y&quot;:0,&quot;width&quot;:300,&quot;height&quot;:300}"', false);

    $this->blade('<x-ui::cropper image="/a.jpg" :default-value="[\'x\' => 1]" />')
        ->assertDontSee('data-value', false);
});

it('renders without an image', function () {
    $this->blade('<x-ui::cropper><x-ui::cropper.image /></x-ui::cropper>')
        ->assertDontSee('data-image', false)
        ->assertSee('<img data-slot="cropper-image" alt=""', false);
});

it('translates its label and description', function () {
    app()->setLocale('zh_CN');

    $this->blade('<x-ui::cropper image="/a.jpg" />')
        ->assertSee('aria-label="图片裁剪"', false)
        ->assertSee('使用鼠标滚轮或双指缩放图片');
});
