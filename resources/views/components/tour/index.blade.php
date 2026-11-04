@blaze

{{-- Not in shadcn (Ant Design's Tour): steps that point at elements of the page one after the other, a spotlight on the element and a panel beside it. Start it from a button (commandfor="name" command="--start") or with Ui::tour('name')->start(); tour-change and tour-close (finished or not) report back. The mask blocks clicks on the page outside the target unless :blocking="false". --}}
@props([
    'name',
    'blocking' => true,
])

<eso-tour {{ $attributes->merge(['id' => $name, 'data-slot' => 'tour']) }}>
    {{ $slot }}
    <div popover="manual" data-slot="tour-spotlight" aria-hidden="true" wire:ignore @if ($blocking) data-blocking @endif></div>
    <div popover="manual" role="dialog" aria-modal="false" data-slot="tour-content" wire:ignore>
        <x-ui::button variant="ghost" size="icon-sm" data-tour-action="close" :aria-label="__('ui::common.close')"><x-ui::icon name="x" /></x-ui::button>
        <div data-slot="tour-body"></div>
        <div data-slot="tour-footer">
            <span data-slot="tour-counter" data-format="{{ __('ui::tour.counter') }}"></span>
            <div data-slot="tour-actions">
                <x-ui::button variant="outline" size="sm" data-tour-action="previous">{{ __('ui::tour.previous') }}</x-ui::button>
                <x-ui::button size="sm" data-tour-action="next">{{ __('ui::tour.next') }}</x-ui::button>
                <x-ui::button size="sm" data-tour-action="finish">{{ __('ui::tour.finish') }}</x-ui::button>
            </div>
        </div>
    </div>
</eso-tour>
<x-ui::extras />
