@blaze

{{-- A thumbnail that opens the viewer. With no slot it shows src; preview-src is the larger image the viewer loads. --}}
@props([
    'src',
    'previewSrc' => null,
    'alt' => '',
])

<button type="button" {{ $attributes->merge([
    'data-slot' => 'image-preview-trigger',
    'data-src' => $previewSrc ?? $src,
    'data-alt' => $alt,
    'aria-haspopup' => 'dialog',
    'aria-label' => $alt === '' ? __('ui::image-preview.open') : false,
]) }}>
    @if ($slot->isEmpty())
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy">
    @else
        {{ $slot }}
    @endif
</button>
