@blaze

@props([
    'orientation' => 'horizontal',
])

{{-- <eso-resizable-panel-group> sizes its panels and moves them with its handles. --}}
<eso-resizable-panel-group {{ $attributes->merge(['data-slot' => 'resizable-panel-group', 'aria-orientation' => $orientation, 'data-orientation' => $orientation]) }}>{{ $slot }}</eso-resizable-panel-group>
