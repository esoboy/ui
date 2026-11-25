@props(['value', 'initialValue', 'inputType' => 'auto', 'outputType' => 'html', 'toolbarPreset' => 'full', 'uploadPastedImages' => true])
@assets
    <link rel="stylesheet" href="{{ \Esoboy\Ui\Support\Assets::url('editor.css') }}" data-navigate-once>
    <script src="{{ \Esoboy\Ui\Support\Assets::url('editor.js') }}" data-navigate-once></script>
@endassets
@php
    $messages = \Esoboy\Ui\Support\EditorTranslations::overrides();
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'editor', get_defined_vars(), compact('inputType', 'outputType', 'toolbarPreset'));
    $wrap = $attributes->has('wire:key') || isset($toolbar) || $slot->hasActualContent();
@endphp
@if ($wrap)<div class="ui-editor-field" wire:key="editor-field-{{ $binding->key }}">@endif
    @isset($toolbar)
        <div class="ui-editor-external-toolbar">{{ $toolbar }}</div>
    @endisset
    <esoboy-editor {{ $attributes->class('ui-editor')->merge([
        'wire:ignore.children' => true,
        'wire:key' => ! $wrap && \Livewire\Livewire::current() ? $binding->key : null,
        'lang' => str_replace('_', '-', app()->getLocale() === 'cn' ? 'zh-CN' : app()->getLocale()),
        'data-messages' => $messages ? json_encode($messages, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) : null,
        'aria-label' => __('ui::editor.editContent'),
        'upload-pasted-images' => filter_var($uploadPastedImages, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
        'input-type' => $inputType,
        'output-type' => $outputType,
        'toolbar-preset' => $toolbarPreset,
        'id' => $binding->id,
        'value' => $binding->config['mode'] === 'controlled' ? ($binding->config['value'] ?? '') : null,
        'initial-value' => $binding->config['mode'] !== 'controlled' ? $binding->config['value'] : null,
    ]) }}></esoboy-editor>
    {{ $slot }}
@if ($wrap)</div>@endif
