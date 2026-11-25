@props(['checked' => false])
@aware(['radioGroup' => null])
@php
    $group = $radioGroup?->attributes;
    $binding = \Esoboy\Ui\Support\ControlAttributes::groupBinding($attributes, $group);
    $checked = \Esoboy\Ui\Support\ControlAttributes::checked($binding, $attributes->get('value'), $checked);
@endphp
<input {{ $attributes->except('disabled')->merge($binding->all())->class('ui-radio ui-radio-group-item')->merge([
    'type' => 'radio', 'name' => $group?->get('name') ?? $radioGroup?->groupName, 'form' => $group?->get('form'),
]) }} @checked($checked) @disabled($group?->get('disabled') || $attributes->get('disabled'))>
