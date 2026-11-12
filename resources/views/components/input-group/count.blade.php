@blaze

@props([
    'max' => null,
])

@php
$characters = \Esoboy\Ui\Support\PendingControl::characters($max === null ? null : (int) $max);
$max = $characters['max'];
$key = \Esoboy\Ui\Support\Model::scope('input-count', $loop ?? null);
@endphp
@livewire('ui.input-count', ['initial' => $characters['value'], 'max' => $max, ...$attributes->merge(['id' => $key])->getAttributes()], key($key))
