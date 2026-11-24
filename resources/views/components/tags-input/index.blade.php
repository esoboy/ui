@props(['size' => 'default', 'value', 'initialValue', 'max' => null, 'maxLength' => 100])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'tags-input', get_defined_vars()))
<div {{ $attributes->only('class')->class('ui-tags-input ui-tags-input-field')->merge(['data-size' => $size]) }}>
    {!! \Livewire\Livewire::mount(
        'ui::tags-input',
        [...$binding->parameters, 'size' => $size, 'max' => $max, 'maxLength' => $maxLength],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div class="ui-tags-input-footer">{{ $footer }}</div>
    @endisset
</div>
