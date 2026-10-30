@blaze

@aware([
    'multiple' => false,
])

@props([
    'value',
])

{{-- A native radio or checkbox covering the row; the questionnaire adds the shortcut letter. --}}
<label {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'questionnaire-choice', 'data-type' => $multiple ? 'checkbox' : 'radio']) }}>
    <input {{ $attributes->except(['class', 'style'])->merge(['type' => $multiple ? 'checkbox' : 'radio', 'value' => $value, 'data-slot' => 'questionnaire-choice-input']) }}>
    <span aria-hidden="true" data-slot="questionnaire-choice-indicator">
        <span data-slot="questionnaire-choice-indicator-dot"></span>
        <x-ui::icon name="check" data-slot="questionnaire-choice-indicator-check" />
    </span>
    <span data-slot="questionnaire-choice-label">{{ $slot }}</span>
    <span data-slot="questionnaire-choice-shortcut" aria-hidden="true" hidden wire:ignore></span>
</label>
