@blaze

@use('Esoboy\Ui\Support\PendingControl')

@props([
    'renderError' => true,
])

@php
// The input inside rendered first and left its validation state behind.
$control = PendingControl::take();
@endphp

<div {{ $attributes->merge(['role' => 'group', 'data-slot' => 'input-group']) }}>{{ $slot }}</div>
{{-- Compound controls can render this error outside their own wrapper. --}}
@if ($renderError)
<x-ui::field.error :state="$control" />
@endif
