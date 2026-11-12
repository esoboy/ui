@blaze

@props(['target' => null, 'title' => null, 'side' => 'bottom', 'align' => 'center'])

<div {{ $attributes->merge([
    'data-tour-step' => true,
    'data-target' => $target ?? false,
    'data-title' => $title ?? false,
    'data-side' => $side,
    'data-align' => $align,
]) }}>{{ $slot }}</div>
