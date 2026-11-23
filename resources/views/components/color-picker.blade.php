@blaze
@props(['invalid' => false])
<span {{ $attributes->only('class')->class('ui-color-picker ui-color-picker-field') }}>
    <input type="color"
        {{ $attributes->except('class')->merge(['id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes), 'aria-invalid' => $invalid ? 'true' : null])->class('ui-color-picker-control') }}>
    <x-ui::icon name="pipette" size="sm" />
</span>
