@blaze
@props(['type' => 'text', 'invalid' => false])
<input
    {{ $attributes->class('ui-input')->merge(['type' => $type, 'data-slot' => 'input', 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}>
