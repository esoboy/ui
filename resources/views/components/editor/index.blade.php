@props(['inputType' => 'auto', 'outputType' => 'html', 'toolbarPreset' => 'full', 'uploadPastedImages' => true, 'value', 'initialValue'])
@php
    $uploadPastedImages = filter_var($uploadPastedImages, FILTER_VALIDATE_BOOLEAN);
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'editor', get_defined_vars(), ['inputType' => $inputType, 'outputType' => $outputType, 'toolbarPreset' => $toolbarPreset, 'uploadPastedImages' => $uploadPastedImages]);
    $disabled = $binding->config['disabled'];
    $readonly = $binding->config['readonly'];
    $messages = \Esoboy\Ui\Support\EditorTranslations::overrides();
@endphp
<div {{ $attributes->only('class')->class('ui-editor ui-editor-field') }}>
    @isset($toolbar)
        <div class="ui-editor-external-toolbar">{{ $toolbar }}</div>
    @endisset
    @assets
        <link rel="stylesheet" href="{{ \Esoboy\Ui\Support\Assets::url('editor.css') }}" data-navigate-once>
        <script src="{{ \Esoboy\Ui\Support\Assets::url('editor.js') }}" data-navigate-once></script>
    @endassets
    <div x-data="uiEditor()"
        data-ui-control="{{ json_encode($binding->config, JSON_THROW_ON_ERROR) }}"
        @if($binding->key) wire:key="{{ $binding->key }}" @endif
        x-on:change="changed($event)" x-on:focusout="blurred($event)" x-on:keydown.enter="entered($event)" {{ $attributes->whereDoesntStartWith(['aria-'])->except(['class', 'wire:key', 'id', 'name'])->class('ui-editor-body') }}>
        <p class="ui-editor-loading" role="status" x-show="!ready && !failed">{{ __('ui::ui.loading') }}</p>
        <p class="ui-editor-error" role="alert" x-show="failed" x-cloak>{{ __('ui::ui.editor_error') }}</p>
        <esoboy-editor wire:ignore.children x-ref="surface" x-on:input.stop x-on:editor-change="edited($event.detail.value)" x-on:editor-error="failed = true"
            input-type="{{ $inputType }}" output-type="{{ $outputType }}"
            lang="{{ app()->getLocale() === 'cn' ? 'zh-CN' : str_replace('_', '-', app()->getLocale()) }}"
            @if($messages) data-messages="{{ json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) }}" @endif
            toolbar-preset="{{ $toolbarPreset }}" x-bind:toolbar-preset="config.toolbarPreset"
            upload-pasted-images="{{ $uploadPastedImages ? 'true' : 'false' }}" x-bind:upload-pasted-images="config.uploadPastedImages ? 'true' : 'false'"
            @disabled($disabled) @readonly($readonly) x-bind:disabled="config.disabled" x-bind:readonly="config.readonly" id="{{ $binding->id }}"
            {{ $attributes->whereStartsWith('aria-')->merge(['aria-label' => __('ui::editor.editContent')]) }}></esoboy-editor>
        @if ($attributes->hasAny(['name', 'form']))
            <input type="hidden" x-ref="input" x-bind:value="formValue" {{ $attributes->only(['name', 'form']) }} value="{{ $binding->config['value'] }}"
                x-bind:disabled="!ready || config.disabled" @disabled($disabled)>
        @endif
    </div>
    {{ $slot }}
</div>
