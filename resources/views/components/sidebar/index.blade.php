@blaze

@use('Esoboy\Ui\Support\SidebarState')

@aware([
    'defaultOpen' => true,
])

@props([
    'side' => 'left',
    'variant' => 'sidebar',
    'collapsible' => 'offcanvas',
])

@php
$open = SidebarState::open($defaultOpen);
@endphp

@if ($collapsible === 'none')
<div {{ $attributes->merge(['data-slot' => 'sidebar']) }}>{{ $slot }}</div>
@else
{{-- Below md the container is a popover the provider opens as a sheet. --}}
<div data-slot="sidebar" data-state="{{ $open ? 'expanded' : 'collapsed' }}" data-collapsible="{{ $open ? '' : $collapsible }}" data-collapsible-mode="{{ $collapsible }}" data-variant="{{ $variant }}" data-side="{{ $side }}">
    <div data-slot="sidebar-gap"></div>
    <div {{ $attributes->merge(['data-slot' => 'sidebar-container', 'data-side' => $side, 'popover' => 'manual']) }}>
        <div data-sidebar="sidebar" data-slot="sidebar-inner">{{ $slot }}</div>
    </div>
</div>
@endif
