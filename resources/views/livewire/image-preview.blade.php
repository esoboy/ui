<div class="contents" data-image-preview-state data-selected="{{ $selected }}">
    <dialog data-slot="image-preview-content" aria-label="{{ __('ui::image-preview.label') }}" wire:ignore.self>
        <img data-slot="image-preview-image" @if ($image) src="{{ $image['src'] }}" @endif alt="{{ $image['alt'] ?? '' }}" draggable="false">
        <x-ui::spinner data-image-preview-spinner />
        @if ($controlsView)
            @include($controlsView, compact('image', 'selected', 'total'))
        @else
            <x-ui::image-preview.action action="close">
                <x-ui::button variant="outline" size="icon-sm">
                    <x-ui::icon name="x" />
                    <span class="sr-only">{{ __('ui::common.close') }}</span>
                </x-ui::button>
            </x-ui::image-preview.action>
            <x-ui::image-preview.action action="previous">
                <x-ui::button variant="outline" size="icon-sm" :disabled="$selected === null || $selected === 0" :hidden="$total < 2">
                    <x-ui::icon name="chevron-left" class="rtl:rotate-180" />
                    <span class="sr-only">{{ __('ui::image-preview.previous') }}</span>
                </x-ui::button>
            </x-ui::image-preview.action>
            <x-ui::image-preview.action action="next">
                <x-ui::button variant="outline" size="icon-sm" :disabled="$selected === null || $selected + 1 >= $total" :hidden="$total < 2">
                    <x-ui::icon name="chevron-right" class="rtl:rotate-180" />
                    <span class="sr-only">{{ __('ui::image-preview.next') }}</span>
                </x-ui::button>
            </x-ui::image-preview.action>
            <div role="toolbar" data-slot="image-preview-toolbar" aria-label="{{ __('ui::image-preview.label') }}">
                <span data-slot="image-preview-counter" @if ($total < 2) hidden @endif>{{ $selected === null ? '' : ($selected + 1).' / '.$total }}</span>
                <x-ui::image-preview.action action="zoom-out">
                    <x-ui::button variant="ghost" size="icon-sm" :aria-label="__('ui::image-preview.zoom_out')">
                        <x-ui::icon name="zoom-out" />
                    </x-ui::button>
                </x-ui::image-preview.action>
                <x-ui::image-preview.action action="zoom-in">
                    <x-ui::button variant="ghost" size="icon-sm" :aria-label="__('ui::image-preview.zoom_in')">
                        <x-ui::icon name="zoom-in" />
                    </x-ui::button>
                </x-ui::image-preview.action>
                <x-ui::image-preview.action action="rotate-left">
                    <x-ui::button variant="ghost" size="icon-sm" :aria-label="__('ui::image-preview.rotate_left')">
                        <x-ui::icon name="rotate-ccw" />
                    </x-ui::button>
                </x-ui::image-preview.action>
                <x-ui::image-preview.action action="rotate-right">
                    <x-ui::button variant="ghost" size="icon-sm" :aria-label="__('ui::image-preview.rotate_right')">
                        <x-ui::icon name="rotate-cw" />
                    </x-ui::button>
                </x-ui::image-preview.action>
                <x-ui::image-preview.action action="reset">
                    <x-ui::button variant="ghost" size="icon-sm" :aria-label="__('ui::image-preview.reset')">
                        <x-ui::icon name="refresh-ccw" />
                    </x-ui::button>
                </x-ui::image-preview.action>
            </div>
        @endif
    </dialog>
</div>
