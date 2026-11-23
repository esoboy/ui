@props(['color' => null, 'value', 'size' => 160])
@php($qr = \Esoboy\Ui\Support\QrCode::matrix($value))
<svg {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'qr', $attributes)->merge(['data-esoboy-ui' => '', 'data-slot' => 'qr-code', 'viewBox' => '0 0 ' . $qr['size'] . ' ' . $qr['size'], 'width' => $size, 'height' => $size, 'role' => 'img', 'aria-label' => $value]) }}
    shape-rendering="crispEdges">
    <rect width="100%" height="100%" fill="white" />
    <path d="{{ $qr['path'] }}" fill="black" />
</svg>
