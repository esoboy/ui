@blaze

@use('Esoboy\Ui\Support\ControlState')

@props([
    'name' => null,
    'messages' => null,
    'bag' => 'default',
    'state' => null,
])

@php
// A control passes its ControlState: the message shows when the field around the control lets it.
if ($state !== null) {
    $messages = $state->showsError() ? $state->messages : [];
    $attributes = $attributes->merge(['id' => $state->errorId]);
}

$messages ??= ControlState::messages($errors ?? null, $name, $bag);
$custom = $slot->hasActualContent();
@endphp

@if ($custom || $messages)
<div {{ $attributes->merge(['role' => 'alert', 'data-slot' => 'field-error']) }}>
    @if ($custom)
        {{ $slot }}
    @elseif (count($messages) === 1)
        {{ $messages[0] }}
    @else
        <ul>
            @foreach ($messages as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif
</div>
@endif
