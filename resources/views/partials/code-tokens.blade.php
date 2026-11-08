@foreach ($tokens as [$text, $class])@if ($class)<span data-token class="{{ $class }}">{{ $text }}</span>@else{{ $text }}@endif@endforeach
