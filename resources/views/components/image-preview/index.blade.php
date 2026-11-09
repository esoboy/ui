@blaze

@use('Esoboy\Ui\Support\Model')

@props(['controlsView' => null])

@php
    $document = \Esoboy\Ui\Support\HtmlFragment::parse((string) $slot);
    $images = [];
    foreach ($document->querySelectorAll('[data-slot="image-preview-trigger"]') as $trigger) {
        if ($trigger->closest('eso-image-preview') === null) {
            $images[] = ['src' => $trigger->getAttribute('data-src') ?? '', 'alt' => $trigger->getAttribute('data-alt') ?? ''];
        }
    }
    $key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('image-preview', $loop ?? null);
@endphp

<eso-image-preview x-data="uiImagePreview" {{ $attributes->merge(['data-slot' => 'image-preview']) }}>
    {{ $slot }}
    @livewire('ui.image-preview', compact('images', 'controlsView'), key('ui-image-preview-'.$key))
</eso-image-preview>
<x-ui::extras />
