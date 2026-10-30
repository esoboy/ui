@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')
@use('Esoboy\Ui\Support\UploadedFiles')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'src' => null,
    'accept' => 'image/*',
    'disabled' => false,
    'shape' => 'square',
    'alt' => '',
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$invalid = $state->invalid();
$model = collect($attributes->getAttributes())->first(fn ($value, $name) => str_starts_with($name, 'wire:model'));
$upload = UploadedFiles::of(Model::value($attributes))[0] ?? null;
$image = $upload['url'] ?? $src;
@endphp

{{-- One image: the new upload, else src. Click or drop to replace; the new image shows before the upload ends. --}}
<eso-file-upload {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'image-upload', 'data-shape' => $shape]) }}>
    <label data-slot="image-upload-dropzone" @if ($invalid) aria-invalid="true" @endif>
        <input {{ $attributes->except(['class', 'style'])->merge(['type' => 'file', 'data-slot' => 'file-upload-input', 'accept' => $accept, 'disabled' => (bool) $disabled, 'aria-label' => __('ui::image-upload.label'), ...$state->attributes()]) }}>
        <img data-slot="image-upload-image" src="{{ $image }}" alt="{{ $alt }}" @if (! $image) hidden @endif>
        <span data-slot="image-upload-placeholder" @if ($image) hidden @endif>
            <x-ui::icon name="image" />
        </span>
        <span data-slot="image-upload-overlay" aria-hidden="true">
            <x-ui::icon name="camera" data-overlay="change" />
            <x-ui::icon name="loader-circle" data-overlay="uploading" />
        </span>
    </label>
    @if ($upload && $model && ! $disabled)
    <x-ui::button variant="secondary" size="icon-xs" data-image-upload-remove :aria-label="__('ui::common.remove')" wire:click="$removeUpload('{{ $model }}', '{{ $upload['filename'] }}')">
        <x-ui::icon name="x" />
    </x-ui::button>
    @endif
    <x-ui::field.error :state="$state" />
</eso-file-upload>
<x-ui::extras />
