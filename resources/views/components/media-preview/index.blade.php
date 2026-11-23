@props(['src', 'type', 'alt' => ''])
@php
    if (!in_array($type ?? null, ['image', 'audio', 'video'], true)) {
        throw new \InvalidArgumentException(__('ui::errors.media_preview_type'));
    }
@endphp

<x-ui::dialog {{ $attributes->except('aria-label')->class('ui-media-preview') }}>
    @if ($slot->hasActualContent())
        <x-ui::dialog.trigger data-ui-media-trigger>{{ $slot }}</x-ui::dialog.trigger>
    @endif

    <x-ui::dialog.content variant="media" class="ui-media-preview-panel" data-type="{{ $type }}" :show-close-button="false"
        :aria-label="$attributes->get('aria-label') ?? ($alt ?: __('ui::ui.preview'))">
        <div @if ($type === 'image') x-data="uiImage" x-effect="if (!opened) { zoom = 1; rotation = 0 }" @endif>
            <div class="ui-media-preview-canvas">
                @if ($type === 'image')
                    <x-ui::image :src="$src" :alt="$alt" x-bind:style="imageStyle($el)" />
                @elseif ($type === 'audio')
                    <audio src="{{ $src }}" controls preload="none" x-bind:preload="opened ? 'metadata' : 'none'"
                        x-effect="if (!opened && !$el.paused) $el.pause()"></audio>
                @else
                    <video src="{{ $src }}" controls playsinline preload="none" x-bind:preload="opened ? 'metadata' : 'none'"
                        x-effect="if (!opened && !$el.paused) $el.pause()"></video>
                @endif
            </div>

            <div class="ui-media-preview-toolbar">
                @if ($type === 'image')
                    <x-ui::button variant="ghost" size="icon" x-on:click="zoom = Math.max(.25, zoom - .25)" :aria-label="__('ui::ui.zoom_out')">
                        <x-ui::icon name="zoom-out" />
                    </x-ui::button>
                    <x-ui::button variant="ghost" size="icon" x-on:click="zoom = Math.min(4, zoom + .25)" :aria-label="__('ui::ui.zoom_in')">
                        <x-ui::icon name="zoom-in" />
                    </x-ui::button>
                    <x-ui::button variant="ghost" size="icon" x-on:click="rotation = (rotation + 90) % 360" :aria-label="__('ui::ui.rotate')">
                        <x-ui::icon name="rotate-cw" />
                    </x-ui::button>
                @endif
                <x-ui::dialog.close>
                    <x-ui::button variant="outline">{{ __('ui::ui.close') }}</x-ui::button>
                </x-ui::dialog.close>
            </div>
        </div>
    </x-ui::dialog.content>
</x-ui::dialog>
