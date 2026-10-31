@blaze

@use('Esoboy\Ui\Support\OverlayModel')

@aware([
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

{!! $model ? '<eso-popover '.$model.'>' : '' !!}
<div {{ $attributes->merge([
    'popover' => 'auto',
    'data-slot' => 'popover-content',
    'data-side' => $side,
    'data-align' => $align,
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
{!! $model ? '</eso-popover>' : '' !!}
