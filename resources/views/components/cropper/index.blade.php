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
    'value' => null,
])

@php
$value = Model::value($attributes, $value);
$value = is_array($value) && isset($value['x'], $value['y'], $value['width'], $value['height']) ? array_map('intval', array_intersect_key($value, array_flip(['x', 'y', 'width', 'height']))) : null;
@endphp

<eso-cropper {{ $attributes->whereDoesntStartWith('wire:model')->merge([
    'data-slot' => 'cropper', 'x-data' => 'uiCropper('.json_encode(Model::binding($attributes)).')',
    'x-on:focusout' => '!$el.contains($event.relatedTarget) && blur()',
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
    <input type="hidden" x-ref="input" value="{{ json_encode($value) }}" {{ Model::input($attributes) }}>
</eso-cropper>
<x-ui::extras />
