@blaze

@use('Esoboy\Ui\Support\QuestionnaireSteps')

@aware([
    'items' => [],
    'defaultItem' => null,
])

@props([
    'variant' => 'default',
    'size' => 'default',
])

@php
$visible = QuestionnaireSteps::visible('next', $items, $defaultItem);
@endphp

{{-- A Button the questionnaire shows only when it applies to the active item. --}}
<x-ui::button :variant="$variant" :size="$size" :attributes="$attributes->merge(['type' => 'button', 'data-questionnaire' => 'next', 'hidden' => ! $visible, 'inert' => ! $visible])">@if ($slot->isEmpty()){{ __('ui::questionnaire.next') }}@else{{ $slot }}@endif</x-ui::button>
