@blaze

@use('Esoboy\Ui\Support\Sequence')

@aware([
    'current' => 1,
])

{{-- status overrides the position: complete, current, upcoming or error. --}}
@props([
    'title' => null,
    'description' => null,
    'status' => null,
])

@php
$step = Sequence::next('steps');
$status ??= $step < (int) $current ? 'complete' : ($step === (int) $current ? 'current' : 'upcoming');
@endphp

<li {{ $attributes->merge(['data-slot' => 'steps-item', 'data-status' => $status, 'aria-current' => $status === 'current' ? 'step' : false]) }}>
    <span data-slot="steps-indicator">
        @if ($status === 'complete')
        <x-ui::icon name="check" />
        @elseif ($status === 'error')
        <x-ui::icon name="x" />
        @else
        {{ $step }}
        @endif
    </span>
    <span data-slot="steps-content">
        @if ($title)<span data-slot="steps-title">{{ $title }}</span>@endif
        @if ($description)<span data-slot="steps-description">{{ $description }}</span>@endif
        {{ $slot }}
    </span>
</li>
