@blaze
@props(['size' => 'default', 'level' => 2])
@php($attributes = $attributes->class('ui-heading')->merge(['data-size' => $size]))
@switch($level)
    @case(1)
        <h1 {{ $attributes }}>{{ $slot }}</h1>
    @break

    @case(2)
        <h2 {{ $attributes }}>{{ $slot }}</h2>
    @break

    @case(3)
        <h3 {{ $attributes }}>{{ $slot }}</h3>
    @break

    @case(4)
        <h4 {{ $attributes }}>{{ $slot }}</h4>
    @break

    @case(5)
        <h5 {{ $attributes }}>{{ $slot }}</h5>
    @break

    @case(6)
        <h6 {{ $attributes }}>{{ $slot }}</h6>
    @break

    @default
        @php(throw new \InvalidArgumentException(__('ui::errors.heading_level')))
@endswitch
