<form id="{{ $jumpId }}-form" method="get" action="{{ $action }}"
    @if ($live) wire:submit="gotoPage($event.target.elements.namedItem(@js($pageName)).valueAsNumber, @js($pageName))" @endif>
    @foreach (array_filter(explode('&', \Illuminate\Support\Arr::query($query)), 'strlen') as $pair)
        @php([$key, $item] = array_pad(explode('=', $pair, 2), 2, ''))
        <input type="hidden" name="{{ urldecode($key) }}" value="{{ urldecode($item) }}">
    @endforeach
</form>
