@blaze

@use('Esoboy\Ui\Support\QuestionnaireSteps')

@aware([
    'items' => [],
    'defaultItem' => null,
])

@props([
    'variant' => 'outline',
    'size' => 'default',
])

@php
$visible = QuestionnaireSteps::visible('skip', $items, $defaultItem);
@endphp

{{-- A Button the questionnaire shows only when it applies to the active item. --}}
<x-ui::button :variant="$variant" :size="$size" :attributes="$attributes->merge(['type' => 'button', 'data-questionnaire' => 'skip', 'hidden' => ! $visible, 'inert' => ! $visible])">@if ($slot->isEmpty()){{ __('ui::questionnaire.skip') }}@else{{ $slot }}@endif</x-ui::button>
