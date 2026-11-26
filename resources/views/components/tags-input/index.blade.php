@props(['size' => 'default', 'value', 'initialValue', 'max' => null, 'maxLength' => 100])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'tags-input', get_defined_vars()))
<{{ $binding->containerTag }} {{ $binding->directives }} {{ $attributes->only(['class', 'wire:key'])->class('ui-tags-input ui-tags-input-field')->merge(['data-size' => $size]) }}>
    @livewire(
        'ui::tags-input',
        [...$binding->parameters, 'size' => $size, 'max' => $max, 'maxLength' => $maxLength],
        key($binding->key))
    {{ $slot }}
    @isset($footer)
        <div class="ui-tags-input-footer">{{ $footer }}</div>
    @endisset
</{{ $binding->containerTag }}>
