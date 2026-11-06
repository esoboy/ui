@blaze

@use('Esoboy\Ui\Support\QrCode')

{{-- Not in shadcn (Ant Design's QRCode): drawn on the server as one SVG path. With an icon covering the middle, the error level defaults to H, which survives the cover. --}}
@props([
    'value',
    'size' => 160,
    'level' => null,
    'icon' => null,
    'iconSize' => 40,
])

@php
$code = QrCode::draw((string) $value, $level ?? ($icon ? 'H' : 'M'));
@endphp

<div {{ $attributes->merge(['role' => 'img', 'aria-label' => __('ui::qr-code.label'), 'data-slot' => 'qr-code']) }}>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {{ $code['modules'] }} {{ $code['modules'] }}" width="{{ $size }}" height="{{ $size }}" shape-rendering="crispEdges" aria-hidden="true"><path fill="currentColor" d="{{ $code['path'] }}"/></svg>
    @if ($icon)
    <img data-slot="qr-code-icon" src="{{ $icon }}" alt="" width="{{ $iconSize }}" height="{{ $iconSize }}">
    @endif
</div>
