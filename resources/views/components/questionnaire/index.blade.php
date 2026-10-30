@blaze

{{-- items (names, or ['name' => ..., 'required' => ...]) lets the server render the first step, progress and actions. --}}
@props([
    'items' => [],
    'defaultItem' => null,
    'shortcuts' => null,
])

<form {{ $attributes->merge(['data-slot' => 'questionnaire', 'novalidate' => true, 'data-shortcuts' => $shortcuts ?? false, 'data-default-item' => $defaultItem ?? false]) }}>{{ $slot }}</form>
