@blaze
@props(['value' => '', 'invalid' => false])
<textarea
    {{ $attributes->class('ui-textarea')->merge(['data-esoboy-ui' => '', 'data-slot' => 'textarea', 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}>{{ $value }}</textarea>
