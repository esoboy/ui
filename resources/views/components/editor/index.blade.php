@blaze

@use('Esoboy\Ui\Assets')
@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\EditorUploads')
@use('Esoboy\Ui\Support\Model')
@use('Esoboy\Ui\Support\RichText')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- Not in shadcn: a Tiptap editor framed as its Textarea. format (text, basic, full, a named format or a list of features) decides all it does: what can be written and what pasted HTML keeps (its whitelist), and the tools it offers; the toolbar fits it unless the slot holds an <x-ui::editor.toolbar>. Ui::editor($name) reaches it by its name, else the property its wire:model binds; a name also keeps a hidden input for forms. The bound HTML is sanitized to the format as it comes in here; the app sanitizes what comes back before storing it, with Ui::sanitize($html, the same format). What it uploads goes to the app's uploader (config ui.editor.uploader), with upload-context, sealed: media picked or dropped, and images on other hosts than the app's (ui.editor.hosts) or pasted as data, when someone saves them or as they come in (ui.editor.auto_upload). --}}
@props([
    'format' => 'full',
    'maxLength' => null,
    'uploadContext' => [],
    'placeholder' => null,
    'label' => null,
    'defaultValue' => null,
    'disabled' => false,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$rich = RichText::resolve($format);
$value = $defaultValue ?? Model::value($attributes);
$value = $rich->sanitize(is_string($value) ? $value : '');
$name = $attributes->get('name');
$counts = $rich->has('character-count') || $maxLength;
$uploads = EditorUploads::enabled() && $rich->has('image', 'audio', 'video', 'file');
$images = $uploads && $rich->has('image');

// The Emoji Picker's data, for :shortcode: suggestions.
$assets = app(Assets::class);
$locale = strtolower(str_replace('_', '-', app()->getLocale()));
$emoji = $rich->has('emoji') ? $assets->url(collect(["emoji/{$locale}.json", 'emoji/'.strtok($locale, '-').'.json', 'emoji/en.json'])->first(fn (string $file) => $assets->path($file) !== null, 'emoji/en.json')) : null;
@endphp

@assets
{!! app(Assets::class)->module('editor.js') !!}
@endassets

{{-- <eso-editor> exposes the HTML as .value ('' when empty), so wire:model binds on it. --}}
<eso-editor {{ $attributes->except('name')->merge([
    'data-slot' => 'editor',
    'data-format' => json_encode($rich->client(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    'data-labels' => json_encode(['expand' => __('ui::editor.expand-details'), 'collapse' => __('ui::editor.collapse-details'), 'task' => __('ui::editor.task-checkbox'), 'caption' => __('ui::editor.caption-placeholder'), 'failed' => __('ui::editor.upload-failed'), 'keyboard' => $rich->has('toolbar', 'bubble-menu') ? __('ui::editor.keyboard-hint') : ''], JSON_UNESCAPED_UNICODE),
    'data-upload' => $uploads ? route('esoboy-ui.editor.upload') : false,
    'data-upload-token' => $uploads ? csrf_token() : false,
    'data-upload-context' => $uploads && $uploadContext ? EditorUploads::seal($uploadContext) : false,
    'data-image-hosts' => $images ? implode(' ', EditorUploads::hosts()) : false,
    'data-auto-upload' => $images && EditorUploads::auto() ? implode(' ', EditorUploads::auto()) : false,
    'data-max-length' => $maxLength ?? false,
    'data-emoji' => $emoji ?? false,
    'data-placeholder' => $placeholder ?? false,
    'data-label' => $label ?? __('ui::editor.label'),
    'data-name' => $name ?? false,
    'data-editor-name' => $name ?? Model::key($attributes) ?? false,
    'data-disabled' => (bool) $disabled,
    ...$state->attributes(),
]) }}>
    @if (! $slot->isEmpty())
    {{ $slot }}
    @elseif ($rich->has('toolbar'))
    <x-ui::editor.toolbar />
    @endif
    @if ($rich->has('find-replace'))
    <x-ui::editor.find />
    @endif
    <div data-slot="editor-content" wire:ignore>{!! $value !!}</div>
    @if ($rich->has('source'))
    <textarea data-slot="editor-source" aria-label="{{ __('ui::editor.source') }}" spellcheck="false" hidden wire:ignore></textarea>
    @endif
    @if ($counts || $uploads)
    {{-- Files on their way (and why a form won't submit meanwhile); images the app has no copy of, to save them all; the count. --}}
    <div data-slot="editor-footer" wire:ignore>
        @if ($uploads)
        <span data-editor-busy data-editor-label="{{ __('ui::editor.uploading-count') }}" data-editor-refused="{{ __('ui::editor.submit-uploading') }}" role="status" hidden></span>
        @endif
        @if ($images)
        <span data-editor-unsaved data-editor-label="{{ __('ui::editor.images-unsaved') }}" hidden><span data-editor-unsaved-label></span><x-ui::button variant="link" size="xs" data-editor-action="save-images">{{ __('ui::editor.save-images') }}</x-ui::button></span>
        @endif
        @if ($counts)
        <span data-editor-characters><span data-editor-count></span> {{ __('ui::editor.characters') }}</span>
        @endif
    </div>
    @endif
    <x-ui::editor.menus />
    <div popover="manual" role="tooltip" data-slot="tooltip-content" data-side="top" data-align="center" data-editor-tip style="--side-offset: 4px;" wire:ignore><span data-editor-tip-label></span><x-ui::kbd data-editor-tip-keys hidden /></div>
    @if ($name)
    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endif
</eso-editor>
<x-ui::field.error :state="$state" />
