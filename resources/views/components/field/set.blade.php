@blaze(fold: true)

{{-- uiField and showError are read by nested controls through @aware. --}}
@props([
    'showError' => true,
    'uiField' => false,
])

<fieldset {{ $attributes->merge(['data-slot' => 'field-set']) }}>{{ $slot }}</fieldset>
