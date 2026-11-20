@blaze
@props(['type' => 'text', 'invalid' => false])
<input
    {{ $attributes->class('ui-input')->merge(['type' => $type, 'data-esoboy-ui' => '', 'data-slot' => 'input', 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}>
