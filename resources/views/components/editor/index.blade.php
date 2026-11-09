@blaze

@use('Esoboy\Ui\Assets')
@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\EditorControls')
@use('Esoboy\Ui\Support\Model')
@use('Esoboy\Ui\Support\RichText')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- Format controls the document and toolbar. Media props enable requests for the application. --}}
@props([
    'format' => 'full',
    'collapseToolbar' => true,
    'maxLength' => null,
    'mediaPicker' => [],
    'mediaUpload' => false,
    'imageHosts' => [],
    'placeholder' => null,
    'label' => null,
    'value' => null,
    'disabled' => false,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$rich = RichText::resolve($format);
$value = Model::value($attributes, $value);
$value = $rich->sanitize(is_string($value) ? $value : '');
$name = $attributes->get('name');
$counts = $rich->has('character-count') || $maxLength;
$uploads = $mediaUpload && $rich->has('image', 'audio', 'video', 'file');
$images = $uploads && $rich->has('image');

$mediaKinds = array_values(array_filter(['image', 'video', 'audio', 'file'], fn ($kind) => in_array($kind, $mediaPicker, true) && $rich->has($kind)));
$slash = EditorControls::slash($rich, $mediaKinds);
$emoji = $rich->has('emoji');
$find = $rich->has('find-replace');
$links = $rich->has('bubble-menu') && $rich->has('link');
$characters = $counts ? RichText::characters($value, $format) : 0;
$key = $attributes->get('wire:key') ?? Model::scope('editor-panels', $loop ?? null);
$ariaLabel = $attributes->get('aria-label');
$ariaLabelledBy = $attributes->get('aria-labelledby');
@endphp


<eso-editor {{ $attributes->whereDoesntStartWith('wire:model')->except(['name', 'aria-label', 'aria-labelledby'])->merge([
    'data-slot' => 'editor', 'x-data' => 'uiEngine('.json_encode(Model::binding($attributes)).')', 'data-engine' => app(Assets::class)->url('editor.js'),
    'data-format' => json_encode($rich->client(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    'data-labels' => json_encode(['expand' => __('ui::editor.expand-details'), 'collapse' => __('ui::editor.collapse-details'), 'task' => __('ui::editor.task-checkbox'), 'caption' => __('ui::editor.caption-placeholder'), 'failed' => __('ui::editor.upload-failed'), 'keyboard' => $rich->has('toolbar', 'bubble-menu') ? __('ui::editor.keyboard-hint') : ''], JSON_UNESCAPED_UNICODE),
    'data-media-upload' => $uploads,
    'data-image-hosts' => $images && $imageHosts ? implode(' ', $imageHosts) : false,
    'data-max-length' => $maxLength ?? false,
    'data-placeholder' => $placeholder ?? false,
    'data-label' => $ariaLabel === null ? ($label ?? __('ui::editor.label')) : html_entity_decode((string) $ariaLabel, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
    'data-labelledby' => $ariaLabelledBy === null ? null : html_entity_decode((string) $ariaLabelledBy, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
    'data-name' => $name ?? false,
    'data-editor-name' => $name ?? Model::key($attributes) ?? false,
    'data-media-picker' => $mediaKinds ? implode(' ', $mediaKinds) : false,
    'data-disabled' => (bool) $disabled,
    ...$state->attributes(),
]) }}>
    @if (! $slot->isEmpty())
    {{ $slot }}
    @elseif ($rich->has('toolbar'))
    <x-ui::editor.toolbar :collapse="$collapseToolbar" />
    @endif
    @if ($find || $counts || $uploads || $slash || $emoji || $links)
    @livewire('ui.editor-panels', compact('slash', 'emoji', 'find', 'counts', 'uploads', 'images', 'maxLength', 'characters', 'links'), key('ui-'.$key))
    @endif
    <div data-slot="editor-content" wire:ignore>{!! $value !!}</div>
    @if ($rich->has('source'))
    <textarea data-slot="editor-source" aria-label="{{ __('ui::editor.source') }}" spellcheck="false" hidden wire:ignore></textarea>
    @endif
    @if ($rich->has('bubble-menu'))
    <div data-slot="editor-bubbles" class="contents" wire:ignore></div>
    @endif
    <x-ui::editor.menus />
    @if ($mediaKinds)
    <p data-slot="editor-field-error" data-editor-media-error role="alert" hidden>{{ __('ui::editor.media-invalid') }}</p>
    @endif
    <input type="hidden" data-editor-model @if ($name) name="{{ $name }}" @endif value="{{ $value }}" {{ Model::input($attributes) }}>
</eso-editor>
<x-ui::field.error :state="$state" />
