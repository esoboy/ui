@props(['src', 'alt' => ''])
<x-ui::dialog {{ $attributes }}><x-ui::dialog.trigger>{{ $trigger }}</x-ui::dialog.trigger><x-ui::dialog.content
        :aria-label="$alt ?: __('ui::ui.preview')" data-slot="image-preview" class="ui-image-preview">
        <div x-data="uiImage" x-effect="if (!opened) { zoom = 1; rotation = 0 }">
            <div data-esoboy-ui data-slot="image-preview-canvas" x-ref="canvas"><x-ui::image :src="$src" :alt="$alt" x-ref="image"
                    x-bind:style="'transform: scale(' + (zoom * (rotation % 180 ? Math.min(1, $refs.canvas.clientWidth / $refs.image.clientHeight, $refs.canvas.clientHeight / $refs.image.clientWidth) : 1)) + ') rotate(' + rotation + 'deg)'" /></div>
            <div data-esoboy-ui data-slot="image-preview-toolbar"><x-ui::button variant="ghost" size="icon"
                    x-on:click="zoom=Math.max(.25,zoom-.25)" :aria-label="__('ui::ui.zoom_out')"><x-ui::icon
                        name="zoom-out" /></x-ui::button><x-ui::button variant="ghost" size="icon"
                    x-on:click="zoom=Math.min(4,zoom+.25)" :aria-label="__('ui::ui.zoom_in')"><x-ui::icon
                        name="zoom-in" /></x-ui::button><x-ui::button variant="ghost" size="icon"
                    x-on:click="rotation=(rotation+90)%360" :aria-label="__('ui::ui.rotate')"><x-ui::icon
                        name="rotate-cw" /></x-ui::button><x-ui::dialog.close><x-ui::button
                        variant="outline">{{ __('ui::ui.close') }}</x-ui::button></x-ui::dialog.close></div>
        </div>{{ $slot }}
    </x-ui::dialog.content></x-ui::dialog>
