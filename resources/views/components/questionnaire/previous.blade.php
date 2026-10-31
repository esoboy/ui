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
$visible = QuestionnaireSteps::visible('previous', $items, $defaultItem);
@endphp

{{-- A Button the questionnaire shows only when it applies to the active item. --}}
<x-ui::button :variant="$variant" :size="$size" :attributes="$attributes->merge(['type' => 'button', 'data-questionnaire' => 'previous', 'hidden' => ! $visible, 'inert' => ! $visible])">@if ($slot->isEmpty()){{ __('ui::questionnaire.previous') }}@else{{ $slot }}@endif</x-ui::button>
