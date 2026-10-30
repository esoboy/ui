@blaze

@aware([
    'image' => null,
])

{{-- The image in a wrapper the script sizes and moves; the class goes to the image, as in Origin UI. --}}
<div data-slot="cropper-image-wrapper" wire:ignore>
    <img {{ $attributes->merge(['data-slot' => 'cropper-image', 'src' => $image ?? false, 'alt' => '', 'draggable' => 'false', 'aria-hidden' => 'true']) }}>
</div>
