@if ($attributes->has('name'))
    @if (is_array($value))
        @foreach ($value as $index => $part)
        <input type="hidden" name="{{ $attributes->get('name') }}[{{ is_string($index) ? $index : '' }}]" value="{{ $part }}">
        @endforeach
    @else
        <input type="hidden" name="{{ $attributes->get('name') }}" value="{{ $value }}">
    @endif
@endif
