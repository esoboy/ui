@blaze

@use('Esoboy\Ui\Support\Sequence')

{{-- current: the 1-based active step; earlier steps are complete, later ones upcoming. --}}
@props([
    'current' => 1,
    'orientation' => 'horizontal',
])

<ol {{ $attributes->merge(['data-slot' => 'steps', 'data-orientation' => $orientation]) }}>{{ $slot }}</ol>
@php
Sequence::reset('steps');
@endphp
