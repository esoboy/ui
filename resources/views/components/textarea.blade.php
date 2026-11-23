@blaze
@props(['value' => '', 'invalid' => false])
<textarea
    {{ $attributes->class('ui-textarea')->merge(['aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}>{{ $value }}</textarea>
