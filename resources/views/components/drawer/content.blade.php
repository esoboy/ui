@blaze

@use('Esoboy\Ui\Support\OverlayModel')
@use('Esoboy\Ui\Support\Model')

@aware([
    'name',
    'uiModel' => null,
    'swipeDirection' => 'down',
    'showSwipeHandle' => false,
])

@php
// wire:model on the root binds whether it is open; see OverlayModel.
$model = OverlayModel::attributes($uiModel);
@endphp

{{-- A modal <dialog> that slides in from the edge it is swiped back to. wire:ignore.self keeps morphs from closing it. --}}
<dialog id="{{ $name }}" data-slot="drawer-popup" data-swipe-direction="{{ $swipeDirection }}" data-swipe-axis="{{ in_array($swipeDirection, ['left', 'right'], true) ? 'x' : 'y' }}" aria-labelledby="{{ $name }}-title" aria-describedby="{{ $name }}-description"
    x-data="uiDialog({{ Js::from(Model::binding($model ?? new \Illuminate\View\ComponentAttributeBag)) }})"
    x-on:toggle="changed()"
    x-on:close="changed()"
    x-on:ui-dialog.window="requested($event.detail)"
    x-on:pointerdown="start($event)"
    x-on:pointermove="move($event)"
    x-on:pointerup="end($event)"
    x-on:pointercancel="end($event)"
    wire:ignore.self>
    @if ($showSwipeHandle)
    <div data-slot="drawer-swipe-handle" aria-hidden="true"></div>
    @endif
    <div {{ $attributes->merge(['data-slot' => 'drawer-content']) }}>{{ $slot }}</div>
</dialog>
