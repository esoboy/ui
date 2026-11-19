@blaze
@props(['orientation' => 'horizontal', 'size' => 'default', 'state' => 'done'])
<div {{ $attributes->class('group/attachment')->merge(['data-slot' => 'attachment', 'data-orientation' => $orientation, 'data-size' => $size, 'data-state' => $state]) }}>{{ $slot }}</div>
