@blaze

@props([
    'orientation' => 'horizontal',
    'align' => 'center',
])

{{-- <eso-carousel> scrolls a native scroll-snap track and enables Previous / Next as it moves. --}}
<eso-carousel {{ $attributes->merge([
    'role' => 'region',
    'aria-roledescription' => __('ui::carousel.label'),
    'data-slot' => 'carousel',
    'data-orientation' => $orientation,
    'data-align' => $align,
]) }}>{{ $slot }}</eso-carousel>
