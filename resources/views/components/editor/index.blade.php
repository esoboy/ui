@blaze

@use('Esoboy\Ui\Assets')
@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\EditorUploads')
@use('Esoboy\Ui\Support\EditorControls')
@use('Esoboy\Ui\Support\Model')
@use('Esoboy\Ui\Support\RichText')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- Format controls the document and toolbar. Media-picker enables application-owned selection.
    Upload-context applies to dropped and pasted files and image imports through ui.editor.uploader. --}}
@props([
    'format' => 'full',
    'collapseToolbar' => true,
    'maxLength' => null,
    'uploadContext' => [],
    'mediaPicker' => [],
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
$uploads = EditorUploads::enabled() && $rich->has('image', 'audio', 'video', 'file');
$images = $uploads && $rich->has('image');

$mediaKinds = array_values(array_filter(['image', 'video', 'audio', 'file'], fn ($kind) => in_array($kind, $mediaPicker, true) && $rich->has($kind)));
$slash = EditorControls::slash($rich, $mediaKinds);
$emoji = $rich->has('emoji');
$find = $rich->has('find-replace');
$links = $rich->has('bubble-menu') && $rich->has('link');
$characters = $counts ? RichText::characters($value, $format) : 0;
$key = $attributes->get('wire:key') ?? Model::scope('editor-panels', $loop ?? null);
@endphp


<eso-editor {{ $attributes->whereDoesntStartWith('wire:model')->except('name')->merge([
    'data-slot' => 'editor', 'x-data' => 'uiEngine('.json_encode(Model::binding($attributes)).')', 'data-engine' => app(Assets::class)->url('editor.js'),
    'data-format' => json_encode($rich->client(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    'data-labels' => json_encode(['expand' => __('ui::editor.expand-details'), 'collapse' => __('ui::editor.collapse-details'), 'task' => __('ui::editor.task-checkbox'), 'caption' => __('ui::editor.caption-placeholder'), 'failed' => __('ui::editor.upload-failed'), 'keyboard' => $rich->has('toolbar', 'bubble-menu') ? __('ui::editor.keyboard-hint') : ''], JSON_UNESCAPED_UNICODE),
    'data-upload' => $uploads ? route('esoboy-ui.editor.upload') : false,
    'data-upload-token' => $uploads ? csrf_token() : false,
    'data-upload-context' => $uploads && $uploadContext ? EditorUploads::seal($uploadContext) : false,
    'data-image-hosts' => $images ? implode(' ', EditorUploads::hosts()) : false,
    'data-auto-upload' => $images && EditorUploads::auto() ? implode(' ', EditorUploads::auto()) : false,
    'data-max-length' => $maxLength ?? false,
    'data-placeholder' => $placeholder ?? false,
    'data-label' => $label ?? __('ui::editor.label'),
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
