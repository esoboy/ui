@blaze
@props(['current' => 1, 'total' => 1])
@php($parts = preg_split('/(:current|:total)/', __('ui::ui.questionnaire_position'), -1, PREG_SPLIT_DELIM_CAPTURE))
<span {{ $attributes->merge(['data-slot' => 'questionnaire-progress', 'role' => 'progressbar', 'aria-label' => __('ui::ui.questionnaire_progress'), 'aria-live' => 'polite', 'aria-valuetext' => __('ui::ui.questionnaire_position', ['current' => $current, 'total' => $total]), 'aria-valuemin' => 1, 'aria-valuemax' => $total, 'aria-valuenow' => $current]) }}>@if($slot->isEmpty())@foreach($parts as $part)@if(!$loop->first)<!---->@endif{{ match($part) { ':current' => $current, ':total' => $total, default => $part } }}@endforeach@else{{ $slot }}@endif</span>
