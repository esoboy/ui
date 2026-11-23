@blaze
@props(['level' => 2])
@switch($level)
    @case(1)
        <h1 {{ $attributes->class('ui-heading') }}>{{ $slot }}</h1>
    @break

    @case(2)
        <h2 {{ $attributes->class('ui-heading') }}>{{ $slot }}</h2>
    @break

    @case(3)
        <h3 {{ $attributes->class('ui-heading') }}>{{ $slot }}</h3>
    @break

    @case(4)
        <h4 {{ $attributes->class('ui-heading') }}>{{ $slot }}</h4>
    @break

    @case(5)
        <h5 {{ $attributes->class('ui-heading') }}>{{ $slot }}</h5>
    @break

    @case(6)
        <h6 {{ $attributes->class('ui-heading') }}>{{ $slot }}</h6>
    @break

    @default
        @php(throw new \InvalidArgumentException(__('ui::errors.heading_level')))
@endswitch
