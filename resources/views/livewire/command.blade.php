<eso-command {{ $attributes->merge([
    'data-slot' => 'command',
    'data-filter' => 'true',
    'data-query' => $query,
    'data-empty' => $empty,
    'x-data' => 'uiCommand',
    'x-on:keydown' => 'key($event)',
    'x-on:input.debounce.100ms' => 'search($event)',
    'x-on:pointermove' => 'hover($event)',
]) }}>{!! $content !!}</eso-command>
