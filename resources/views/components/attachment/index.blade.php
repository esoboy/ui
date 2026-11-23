@blaze
@props(['orientation' => 'horizontal', 'size' => 'default', 'state' => 'done'])
<div {{ $attributes->class('ui-attachment group/attachment')->merge(['data-orientation' => $orientation, 'data-size' => $size, 'data-state' => $state]) }}>{{ $slot }}</div>
