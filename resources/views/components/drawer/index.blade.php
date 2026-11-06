@blaze(fold: true)

{{-- The name is the <dialog> id: triggers point at it and Ui::dialog($name) opens it. --}}
@props([
    'name',
    'swipeDirection' => 'down',
    'showSwipeHandle' => false,
])

{{ $slot }}
