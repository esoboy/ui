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
$visible = QuestionnaireSteps::visible('submit', $items, $defaultItem);
@endphp

{{-- A Button the questionnaire shows only when it applies to the active item. --}}
<x-ui::button :variant="$variant" :size="$size" :attributes="$attributes->merge(['type' => 'submit', 'data-questionnaire' => 'submit', 'hidden' => ! $visible, 'inert' => ! $visible])">@if ($slot->isEmpty()){{ __('ui::questionnaire.submit') }}@else{{ $slot }}@endif</x-ui::button>
