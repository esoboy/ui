@blaze(fold: true)

{{-- Holds the scroll behaviour props, as shadcn's provider does; the scroller reads them. --}}
@props([
    'autoScroll' => false,
    'scrollPosition' => 'end',
    'scrollEdgeThreshold' => 8,
    'scrollPreviousItemPeek' => 64,
    'scrollMargin' => 0,
])

{{ $slot }}
