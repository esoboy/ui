@blaze

@use('Esoboy\Ui\Support\EditorUploads')
@use('Esoboy\Ui\Support\RichText')

@aware([
    'format' => 'full',
])

{{-- A video, as the format has them: uploaded (with the app's uploader, as other media are), or from a link: a video file's address plays in a player, a platform's share link or embed code in its own. --}}
@php
$rich = RichText::resolve($format);
$placeholder = match (true) {
    $rich->has('video') && $rich->has('embed') => __('ui::editor.video-placeholder'),
    $rich->has('video') => __('ui::editor.video-file-placeholder'),
    default => __('ui::editor.embed-placeholder'),
};
@endphp

<x-ui::popover>
    <x-ui::popover.trigger variant="ghost" size="icon-sm" data-editor-opens="video" :aria-label="__('ui::editor.video')" :attributes="$attributes"><x-ui::icon name="video" /></x-ui::popover.trigger>
    <x-ui::popover.content align="start" data-editor-field-content>
        @if ($rich->has('video') && EditorUploads::enabled())
        <x-ui::button variant="outline" size="sm" data-editor-action="media" data-editor-value="video" data-editor-upload><x-ui::icon name="upload" />{{ __('ui::editor.video-upload') }}</x-ui::button>
        @endif
        <x-ui::input data-editor-video-input :placeholder="$placeholder" :aria-label="__('ui::editor.video-link')" />
        <x-ui::button size="icon-sm" data-editor-action="video-apply" :aria-label="__('ui::editor.video-apply')"><x-ui::icon name="corner-down-left" /></x-ui::button>
        <p data-slot="editor-field-error">{{ __('ui::editor.video-invalid') }}</p>
    </x-ui::popover.content>
</x-ui::popover>
