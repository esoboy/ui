@props(['side' => 'bottom', 'align' => 'end'])
<div {{ $attributes->merge(['data-slot' => 'bubble-reactions', 'data-side' => $side, 'data-align' => $align]) }}>{{ $slot }}</div>
