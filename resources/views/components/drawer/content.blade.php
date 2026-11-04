@blaze

@use('Esoboy\Ui\Support\OverlayModel')

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

{!! $model ? '<eso-dialog '.$model.'>' : '' !!}
{{-- A modal <dialog> that slides in from the edge it is swiped back to. wire:ignore.self keeps morphs from closing it. --}}
<dialog id="{{ $name }}" data-slot="drawer-popup" data-swipe-direction="{{ $swipeDirection }}" data-swipe-axis="{{ in_array($swipeDirection, ['left', 'right'], true) ? 'x' : 'y' }}" aria-labelledby="{{ $name }}-title" aria-describedby="{{ $name }}-description" wire:ignore.self>
    @if ($showSwipeHandle)
    <div data-slot="drawer-swipe-handle" aria-hidden="true"></div>
    @endif
    <div {{ $attributes->merge(['data-slot' => 'drawer-content']) }}>{{ $slot }}</div>
</dialog>
{!! $model ? '</eso-dialog>' : '' !!}
