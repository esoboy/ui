@blaze

@use('Esoboy\Ui\Support\OverlayModel')

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

{!! $model ? '<eso-dialog '.$model.'>' : '' !!}
{{-- wire:ignore.self keeps Livewire morphs from stripping the open state. --}}
<dialog {{ $attributes->merge([
    'id' => $name,
    'role' => 'alertdialog',
    'data-slot' => 'alert-dialog-content',
    'data-size' => $size,
    'aria-labelledby' => $name.'-title',
    'aria-describedby' => $name.'-description',
]) }} wire:ignore.self>{{ $slot }}</dialog>
{!! $model ? '</eso-dialog>' : '' !!}
