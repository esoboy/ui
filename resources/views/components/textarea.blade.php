@blaze
@props(['size' => 'default', 'value' => '', 'invalid' => false])
<textarea
    {{ $attributes->class('ui-textarea ui-text-control')->merge(['data-size' => $size, 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}>{{ $value }}</textarea>
