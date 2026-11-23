@props(['target', 'index'])
<section {{ $attributes->merge(['class' => 'ui-tour-step', 'data-target' => $target, 'data-index' => $index]) }}
    x-show="index === @js($index)">{{ $slot }}</section>
