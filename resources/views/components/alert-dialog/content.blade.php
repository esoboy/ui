@blaze

@use('Esoboy\Ui\Support\OverlayModel')
@use('Esoboy\Ui\Support\Model')

@aware([
    'name',
    'uiModel' => null,
])

@props([
    'size' => 'default',
])

@php
// wire:model on the root binds whether it is open; see OverlayModel.
$model = OverlayModel::attributes($uiModel);
@endphp

{{-- wire:ignore.self keeps Livewire morphs from stripping the open state. --}}
<dialog {{ $attributes->merge([
    'id' => $name,
    'x-data' => 'uiDialog('.json_encode(Model::binding($model ?? new \Illuminate\View\ComponentAttributeBag)).')',
    'x-on:toggle' => 'changed()',
    'x-on:close' => 'changed()',
    'x-on:ui-dialog.window' => 'requested($event.detail)',
    'x-on:click' => 'backdrop($event)',
    'x-on:pointerdown' => 'start($event)',
    'x-on:pointermove' => 'move($event)',
    'x-on:pointerup' => 'end($event)',
    'x-on:pointercancel' => 'end($event)',
    'role' => 'alertdialog',
    'data-slot' => 'alert-dialog-content',
    'data-size' => $size,
    'aria-labelledby' => $name.'-title',
    'aria-describedby' => $name.'-description',
]) }} wire:ignore.self>{{ $slot }}</dialog>
