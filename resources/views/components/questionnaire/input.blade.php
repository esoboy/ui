@blaze
@props(['optionalName' => null])
<input @if ($optionalName !== null) data-answer-name="{{ $optionalName }}" x-on:input.capture="if ($el.value) $el.name = $el.dataset.answerName; else $el.removeAttribute('name')" @endif {{ $attributes->merge(['class' => 'ui-questionnaire-input', 'type' => 'text']) }}>
