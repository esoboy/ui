@blaze(fold: true)

@props([
    'orientation' => 'vertical',
    'columns' => 1,
])

<dl {{ $attributes->merge(['data-slot' => 'description-list', 'data-orientation' => $orientation, 'style' => "--columns: {$columns};"]) }}>{{ $slot }}</dl>
