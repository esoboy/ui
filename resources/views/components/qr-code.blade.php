@props(['value', 'size' => 160])
@php($qr = \Esoboy\Ui\Support\QrCode::matrix($value))
<svg {{ $attributes->merge(['class' => 'ui-qr-code', 'viewBox' => '0 0 ' . $qr['size'] . ' ' . $qr['size'], 'width' => $size, 'height' => $size, 'role' => 'img', 'aria-label' => $value]) }}
    shape-rendering="crispEdges">
    <rect width="100%" height="100%" fill="var(--ui-qr-code-bg, white)" />
    <path d="{{ $qr['path'] }}" fill="var(--ui-qr-code-fg, black)" />
</svg>
