@props(['initialOpen' => true, 'collapsible' => 'offcanvas', 'variant' => 'sidebar', 'side' => 'left'])
@php
    if (!in_array($collapsible, ['offcanvas', 'icon', 'none'], true) || !in_array($variant, ['sidebar', 'floating', 'inset'], true) || !in_array($side, ['left', 'right'], true)) throw new \InvalidArgumentException(__('ui::errors.sidebar_mode'));
@endphp
<div {{ $attributes->class('group/sidebar-wrapper')->merge(['class' => 'ui-sidebar ui-sidebar-variant-'.$variant, 'data-collapsible' => $collapsible, 'data-side' => $side]) }} data-ui-overlay
    x-data="uiSidebar(@js($initialOpen), @js($collapsible))" x-modelable="opened" x-bind:data-state="opened ? 'expanded' : 'collapsed'" x-bind:data-mobile="mobile ? 'true' : 'false'" x-id="['ui-sidebar']">
    {{ $slot }}
</div>
