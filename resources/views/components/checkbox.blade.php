@blaze
@props(['checked' => false, 'indeterminate' => false, 'invalid' => false])
<input
    {{ $attributes->class('ui-checkbox')->merge(['type' => 'checkbox', 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}
    @checked($checked) x-init="" x-effect="$el.indeterminate = @js($indeterminate)">
