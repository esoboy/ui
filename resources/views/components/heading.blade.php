@blaze
@props(['color' => null, 'level' => 2])
@php
    $attributes = \Esoboy\Ui\Support\ComponentColor::attributes($color, 'ink', $attributes);
@endphp
@switch($level)
    @case(1)
        <h1 {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'heading']) }}>{{ $slot }}</h1>
    @break

    @case(2)
        <h2 {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'heading']) }}>{{ $slot }}</h2>
    @break

    @case(3)
        <h3 {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'heading']) }}>{{ $slot }}</h3>
    @break

    @case(4)
        <h4 {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'heading']) }}>{{ $slot }}</h4>
    @break

    @case(5)
        <h5 {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'heading']) }}>{{ $slot }}</h5>
    @break

    @case(6)
        <h6 {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'heading']) }}>{{ $slot }}</h6>
    @break

    @default
        @php(throw new \InvalidArgumentException(__('ui::errors.heading_level')))
@endswitch
