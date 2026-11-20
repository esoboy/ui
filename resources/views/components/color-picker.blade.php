@blaze
@props(['invalid' => false])
<span data-esoboy-ui data-slot="color-picker-field" {{ $attributes->only('class')->class('ui-color-picker-field') }}>
    <input type="color" data-esoboy-ui data-slot="color-picker"
        {{ $attributes->except('class')->merge(['id' => \Esoboy\Ui\Support\ControlAttributes::id($attributes), 'aria-invalid' => $invalid ? 'true' : null]) }}>
    <x-ui::icon name="pipette" size="sm" />
</span>
