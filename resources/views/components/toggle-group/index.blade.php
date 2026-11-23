@props(['type' => 'single', 'variant' => 'default', 'size' => 'default', 'disabled' => false, 'orientation' => 'horizontal', 'spacing' => 2])
@php
    if (!in_array($variant, ['default', 'outline'], true) || !in_array($size, ['sm', 'default', 'lg'], true)) throw new \InvalidArgumentException(__('ui::errors.toggle_style'));
    if (!in_array($type, ['single', 'multiple'], true)) throw new \InvalidArgumentException(__('ui::errors.toggle_group_type'));
    if (!in_array($orientation, ['horizontal', 'vertical'], true) || !is_numeric($spacing) || $spacing < 0 || $spacing > 12) throw new \InvalidArgumentException(__('ui::errors.toggle_group_layout'));
@endphp
<div data-orientation="{{ $orientation }}" data-spacing="{{ $spacing }}" data-size="{{ $size }}" x-data="uiChoiceGroup" x-on:keydown="navigate($event)" role="{{ $type === 'single' ? 'radiogroup' : 'group' }}" {{ $attributes->style('--toggle-spacing: '.($spacing * .25).'rem')->class('ui-toggle-group group/toggle-group') }}>{{ $slot }}</div>
