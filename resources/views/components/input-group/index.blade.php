@blaze

@use('Esoboy\Ui\Support\PendingControl')

@props([
    'renderError' => true,
])

@php
// The input inside rendered first and left its validation state behind.
$control = PendingControl::take();
@endphp

<div {{ $attributes->merge(['role' => 'group', 'data-slot' => 'input-group', 'x-data' => '{}', 'x-on:click' => '$event.target.closest(\'[data-slot="input-group-addon"]\') && ! $event.target.closest(\'button\') && $el.querySelector(\'[data-slot="input-group-control"]\')?.focus()', 'x-on:input' => '$el.querySelectorAll(\'[data-slot="input-group-count"]\').forEach(count => Alpine.$data(count).queue($event))']) }}>{{ $slot }}</div>
{{-- Compound controls can render this error outside their own wrapper. --}}
@if ($renderError)
<x-ui::field.error :state="$control" />
@endif
