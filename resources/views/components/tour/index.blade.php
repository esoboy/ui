@blaze

@props(['name', 'blocking' => true, 'actionsView' => null])

@php
    $steps = \Esoboy\Ui\Support\TourSteps::read((string) $slot);
    $stepSlots = [];
    foreach ($steps as $index => &$step) {
        $stepSlots['step-'.$index] = new \Illuminate\View\ComponentSlot($step['description']);
        $step['description'] = '';
    }
    unset($step);
@endphp

<eso-tour {{ $attributes->merge(['id' => $name, 'data-slot' => 'tour']) }}>
    {!! \Esoboy\Ui\Support\ViewSlots::mount('ui.tour', [
        'steps' => $steps,
        'blocking' => (bool) $blocking,
        'actionsView' => $actionsView,
    ], 'ui-tour-'.$name, $stepSlots) !!}
</eso-tour>
<x-ui::extras />
