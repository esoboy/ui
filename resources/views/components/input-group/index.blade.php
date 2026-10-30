@blaze

@use('Esoboy\Ui\Support\PendingControl')

@php
// The input inside rendered first and left its validation state behind.
$control = PendingControl::take();
@endphp

<div {{ $attributes->merge(['role' => 'group', 'data-slot' => 'input-group']) }}>{{ $slot }}</div>
<x-ui::field.error :state="$control" />
