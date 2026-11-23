@props(['color' => null, 'value', 'initialValue', 'max' => null, 'maxLength' => 100])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'tags-input', get_defined_vars()))
<div data-esoboy-ui data-slot="tags-input-field">
    {!! \Livewire\Livewire::mount(
        'ui::tags-input',
        [...$binding->parameters, 'color' => $color, 'max' => $max, 'maxLength' => $maxLength],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div data-esoboy-ui data-slot="tags-input-footer">{{ $footer }}</div>
    @endisset
</div>
