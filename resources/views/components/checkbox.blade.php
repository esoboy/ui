@blaze
@props(['size' => 'default', 'checked' => false, 'indeterminate' => null, 'invalid' => false])
<input
    {{ $attributes->class('ui-checkbox')->merge(['data-size' => $size, 'type' => 'checkbox', 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes, choice: true)]) }}
    @checked($checked) @if($indeterminate !== null) x-data x-effect="$el.indeterminate = @js($indeterminate)" @endif>
