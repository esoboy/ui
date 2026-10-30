@blaze

@props([
    'direction' => 'end',
    'variant' => 'secondary',
    'size' => 'icon-sm',
    'behavior' => 'smooth',
])

{{-- Inert until there is content in its direction; the scroller switches data-active. --}}
<x-ui::button :variant="$variant" :size="$size" :attributes="$attributes->merge(['data-message-scroller-button' => $direction, 'data-direction' => $direction, 'data-behavior' => $behavior, 'data-active' => 'false', 'inert' => true, 'tabindex' => '-1'])" wire:ignore.self>
    @if ($slot->isEmpty())
    <x-ui::icon name="arrow-down" />
    <span class="sr-only">{{ $direction === 'end' ? __('ui::message-scroller.end') : __('ui::message-scroller.start') }}</span>
    @else
    {{ $slot }}
    @endif
</x-ui::button>
