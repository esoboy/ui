@props(['selected' => false])
@aware(['toggleGroup' => null])
@php
    $type = $toggleGroup?->type ?? 'single';
    $variant = $toggleGroup?->variant ?? 'default';
    $size = $toggleGroup?->size ?? 'default';
    $group = $toggleGroup?->attributes;
    $binding = \Esoboy\Ui\Support\ControlAttributes::groupBinding($attributes, $group);
    $selected = \Esoboy\Ui\Support\ControlAttributes::checked($binding, $attributes->get('value'), $selected);
@endphp
<label class="ui-toggle-group-item ui-toggle-group-item-variant-{{ $variant }} {{ $attributes->get('class') }}" data-size="{{ $size }}"><input type="{{ $type === 'multiple' ? 'checkbox' : 'radio' }}" {{ $attributes->except(['class', 'disabled'])->merge($binding->all())->merge(['name' => $group?->get('name') ?? ($type === 'single' ? $toggleGroup?->groupName : null), 'form' => $group?->get('form')]) }} @checked($selected)
        @disabled($toggleGroup?->disabled || $attributes->get('disabled')) class="ui-visually-hidden"><span class="ui-toggle-control ui-toggle-control-variant-{{ $variant }}"
        data-size="{{ $size }}">{{ $slot }}</span></label>
