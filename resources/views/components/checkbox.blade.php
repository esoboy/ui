@blaze
@props(['color' => null, 'checked' => false, 'indeterminate' => false, 'invalid' => false])
@php
    $attributes = \Esoboy\Ui\Support\ComponentColor::attributes($color, 'checkbox', $attributes);
@endphp
<input
    {{ $attributes->class('ui-checkbox')->merge(['type' => 'checkbox', 'data-esoboy-ui' => '', 'data-slot' => 'checkbox', 'aria-invalid' => $invalid ? 'true' : null, 'id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes)]) }}
    @checked($checked) x-init="" x-effect="$el.indeterminate = @js($indeterminate)">
