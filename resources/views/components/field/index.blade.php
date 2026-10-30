@blaze(fold: true)

{{-- uiField and showError are read by nested controls through @aware. --}}
@props([
    'orientation' => 'vertical',
    'showError' => true,
    'uiField' => false,
])

<div {{ $attributes->merge(['role' => 'group', 'data-slot' => 'field', 'data-orientation' => $orientation]) }}>{{ $slot }}</div>
