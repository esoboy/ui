@blaze
@props(['checked' => false])
<input {{ $attributes->class('ui-radio')->merge(['type' => 'radio', 'class' => 'ui-radio-group-item']) }}
    @checked($checked)>
