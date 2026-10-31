@blaze

{{-- The frame of the crop, sized by the script; class="rounded-full" shows a round avatar's crop. --}}
<div {{ $attributes->merge(['data-slot' => 'cropper-crop-area', 'aria-hidden' => 'true']) }} hidden wire:ignore></div>
