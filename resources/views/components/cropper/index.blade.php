@blaze

@use('Esoboy\Ui\Support\Model')

{{-- Not in shadcn: Origin UI's Cropper, with its screen reader description built in. Give it a height (class="h-80"); wire:model gets the crop { x, y, width, height } in the image's pixels, to cut on the server. --}}
@props([
    'image' => null,
    'aspectRatio' => 1,
    'cropPadding' => 25,
    'minZoom' => 1,
    'maxZoom' => 3,
    'zoomSensitivity' => 0.005,
    'keyboardStep' => 10,
    'zoom' => null,
    'defaultValue' => null,
])

@php
$value = $defaultValue ?? Model::value($attributes);
$value = is_array($value) && isset($value['x'], $value['y'], $value['width'], $value['height']) ? array_map('intval', array_intersect_key($value, array_flip(['x', 'y', 'width', 'height']))) : null;
@endphp

{{-- <eso-cropper> exposes the crop as .value, so wire:model binds on it. --}}
<eso-cropper {{ $attributes->merge([
    'data-slot' => 'cropper',
    'role' => 'application',
    'tabindex' => '0',
    'aria-label' => __('ui::cropper.label'),
    'aria-description' => __('ui::cropper.description'),
    'data-image' => $image ?? false,
    'data-aspect-ratio' => $aspectRatio,
    'data-crop-padding' => $cropPadding,
    'data-min-zoom' => $minZoom,
    'data-max-zoom' => $maxZoom,
    'data-zoom-sensitivity' => $zoomSensitivity,
    'data-keyboard-step' => $keyboardStep,
    'data-zoom' => $zoom ?? false,
    'data-value' => $value ? json_encode($value) : false,
]) }}>
    {{ $slot }}
</eso-cropper>
<x-ui::extras />
