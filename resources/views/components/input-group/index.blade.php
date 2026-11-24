@blaze
@props(['size' => null])
@php($size ??= \Esoboy\Ui\View\ButtonGroup::defaultSize())
<div {{ $attributes->class('ui-input-group group/input-group')->merge(['role' => 'group', 'data-size' => $size]) }}>{{ $slot }}</div>
