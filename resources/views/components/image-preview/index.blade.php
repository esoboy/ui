@blaze

{{-- The images inside open in one viewer and page through each other. --}}
<eso-image-preview {{ $attributes->merge(['data-slot' => 'image-preview']) }}>
    {{ $slot }}
    {{-- wire:ignore keeps Livewire morphs out of the open viewer. --}}
    <dialog data-slot="image-preview-content" aria-label="{{ __('ui::image-preview.label') }}" wire:ignore>
        <img data-slot="image-preview-image" alt="" draggable="false">
        <x-ui::spinner data-image-preview-spinner />
        <x-ui::button variant="outline" size="icon-sm" data-image-preview="close">
            <x-ui::icon name="x" />
            <span class="sr-only">{{ __('ui::common.close') }}</span>
        </x-ui::button>
        <x-ui::button variant="outline" size="icon-sm" data-image-preview="previous">
            <x-ui::icon name="chevron-left" class="rtl:rotate-180" />
            <span class="sr-only">{{ __('ui::image-preview.previous') }}</span>
        </x-ui::button>
        <x-ui::button variant="outline" size="icon-sm" data-image-preview="next">
            <x-ui::icon name="chevron-right" class="rtl:rotate-180" />
            <span class="sr-only">{{ __('ui::image-preview.next') }}</span>
        </x-ui::button>
        <div role="toolbar" data-slot="image-preview-toolbar" aria-label="{{ __('ui::image-preview.label') }}">
            <span data-slot="image-preview-counter"></span>
            <x-ui::button variant="ghost" size="icon-sm" data-image-preview="zoom-out" :aria-label="__('ui::image-preview.zoom_out')">
                <x-ui::icon name="zoom-out" />
            </x-ui::button>
            <x-ui::button variant="ghost" size="icon-sm" data-image-preview="zoom-in" :aria-label="__('ui::image-preview.zoom_in')">
                <x-ui::icon name="zoom-in" />
            </x-ui::button>
            <x-ui::button variant="ghost" size="icon-sm" data-image-preview="rotate-left" :aria-label="__('ui::image-preview.rotate_left')">
                <x-ui::icon name="rotate-ccw" />
            </x-ui::button>
            <x-ui::button variant="ghost" size="icon-sm" data-image-preview="rotate-right" :aria-label="__('ui::image-preview.rotate_right')">
                <x-ui::icon name="rotate-cw" />
            </x-ui::button>
            <x-ui::button variant="ghost" size="icon-sm" data-image-preview="reset" :aria-label="__('ui::image-preview.reset')">
                <x-ui::icon name="refresh-ccw" />
            </x-ui::button>
        </div>
    </dialog>
</eso-image-preview>
<x-ui::extras />
