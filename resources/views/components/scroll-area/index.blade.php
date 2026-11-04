@blaze

{{-- <eso-scroll-area> hides the native scrollbars and draws shadcn's thin ones over the viewport. --}}
<eso-scroll-area {{ $attributes->merge(['data-slot' => 'scroll-area']) }}>
    <div data-slot="scroll-area-viewport" tabindex="0">{{ $slot }}</div>
    <div data-slot="scroll-area-scrollbar" data-orientation="vertical" hidden wire:ignore><div data-slot="scroll-area-thumb"></div></div>
    <div data-slot="scroll-area-scrollbar" data-orientation="horizontal" hidden wire:ignore><div data-slot="scroll-area-thumb"></div></div>
</eso-scroll-area>
