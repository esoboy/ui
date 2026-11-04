@blaze

@aware([
    'align' => 'start',
])

<div {{ $attributes->merge([
    'popover' => 'auto',
    'data-slot' => 'navigation-menu-content',
    'data-side' => 'bottom',
    'data-align' => $align,
    'style' => '--side-offset: 8px;',
]) }}>{{ $slot }}</div>
