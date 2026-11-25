@props(['initialOpen' => true, 'collapsible' => 'offcanvas', 'variant' => 'sidebar', 'side' => 'left'])
@php
    if (!in_array($collapsible, ['offcanvas', 'icon', 'none'], true) || !in_array($variant, ['sidebar', 'floating', 'inset'], true) || !in_array($side, ['left', 'right'], true)) throw new \InvalidArgumentException(__('ui::errors.sidebar_mode'));
@endphp
@includeIsolated('ui::partials.panel-state', ['kind' => 'sidebar', 'initial' => $initialOpen, 'settings' => compact('collapsible', 'variant', 'side'), 'attributes' => $attributes, 'slot' => $slot])
