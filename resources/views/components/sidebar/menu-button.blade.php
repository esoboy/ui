@blaze

@props([
    'active' => false,
    'variant' => 'default',
    'size' => 'default',
    'tooltip' => null,
])

@php
// The tooltip only shows while the sidebar is collapsed to icons (sidebar.css hides it otherwise).
$tooltipId = \Esoboy\Ui\Support\Model::scope('ui-sidebar-tip-'.\Esoboy\Ui\Support\Sequence::next('sidebar-tooltip'), $loop ?? null);
$attributes = $attributes->merge([
    'data-slot' => 'sidebar-menu-button',
    'data-sidebar' => 'menu-button',
    'data-variant' => $variant,
    'data-size' => $size,
    'data-active' => $active,
    'data-tooltip-trigger' => $tooltip !== null, ...($tooltip !== null ? ['data-ui-tooltip' => $tooltipId, 'x-data' => 'uiOverlayTrigger', 'x-on:pointerover' => 'enter($event)', 'x-on:pointerout' => 'leave($event)', 'x-on:focusin' => 'enter($event)', 'x-on:focusout' => 'leave($event)'] : []),
]);
@endphp

@if ($attributes->has('href'))
<a {{ $attributes }}>{{ $slot }}</a>
@else
<button {{ $attributes->merge(['type' => 'button']) }}>{{ $slot }}</button>
@endif
@if ($tooltip !== null)
<x-ui::tooltip.content :id="$tooltipId" side="right" align="center" data-sidebar="menu-tooltip">{{ $tooltip }}</x-ui::tooltip.content>
@endif
