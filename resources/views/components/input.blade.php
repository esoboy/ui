@blaze
@props(['type' => 'text', 'invalid' => false])
<input
    {{ $attributes->class('ui-input ui-text-control')->merge(['type' => $type, 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}>
