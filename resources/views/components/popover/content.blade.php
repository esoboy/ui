@blaze

@use('Esoboy\Ui\Support\OverlayModel')

@aware([
    'uiPopoverId' => null,
    'uiModel' => null,
])

@props([
    'side' => 'bottom',
    'align' => 'center',
    'sideOffset' => 4,
    'alignOffset' => 0,
])

@php
// wire:model on the root binds whether it is open; see OverlayModel.
$model = OverlayModel::attributes($uiModel);
@endphp

<div {{ $attributes->merge(['id' => $uiPopoverId, 'x-data' => 'uiOverlayContent('.json_encode(\Esoboy\Ui\Support\Model::binding($model ?? new \Illuminate\View\ComponentAttributeBag)).')', 'x-on:toggle' => 'toggled($event)', 'x-on:pointerover' => 'enter()', 'x-on:pointerout' => 'leave($event)', 'x-on:pointermove' => 'hover($event)', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)', 'x-on:keydown.escape.window' => 'escape()',
    'popover' => 'auto',
    'data-slot' => 'popover-content',
    'data-side' => $side,
    'data-align' => $align,
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
