@blaze
@props(['size' => null, 'type' => 'text', 'invalid' => false])
@php
    $nativeSize = is_numeric($size) ? $size : null;
    $visualSize = $nativeSize !== null || $size === null ? \Esoboy\Ui\View\ButtonGroup::defaultSize() : $size;
@endphp
<input
    {{ $attributes->class('ui-input ui-text-control')->merge(['size' => $nativeSize, 'data-size' => $visualSize, 'type' => $type, 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}>
