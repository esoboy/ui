@blaze

@aware([
    'items' => [],
])

@php
$total = count($items);
@endphp

{{-- The questionnaire keeps the text current; wire:ignore stops morphs resetting it. --}}
<div {{ $attributes->merge([
    'role' => 'progressbar',
    'aria-label' => __('ui::questionnaire.progress_label'),
    'aria-live' => 'polite',
    'aria-valuemin' => $total ? 1 : false,
    'aria-valuemax' => $total ?: false,
    'aria-valuenow' => $total ? 1 : false,
    'data-slot' => 'questionnaire-progress',
    'data-template' => __('ui::questionnaire.progress', ['current' => ':current', 'total' => ':total']),
]) }} wire:ignore>@if ($slot->isEmpty()){{ $total ? __('ui::questionnaire.progress', ['current' => 1, 'total' => $total]) : '' }}@else{{ $slot }}@endif</div>
