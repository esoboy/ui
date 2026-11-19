@blaze
@props(['checked' => false])
<input {{ $attributes->class('ui-radio')->merge(['type' => 'radio', 'data-slot' => 'radio-group-item']) }}
    @checked($checked)>
