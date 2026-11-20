@blaze
@props(['checked' => false])
<input {{ $attributes->class('ui-radio')->merge(['type' => 'radio', 'data-esoboy-ui' => '', 'data-slot' => 'radio-group-item']) }}
    @checked($checked)>
