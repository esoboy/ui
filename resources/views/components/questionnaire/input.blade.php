@blaze

@aware([
    'name',
])

{{-- A freeform answer; give it a visible label or aria-label. --}}
<div data-slot="questionnaire-input-wrapper">
    <input {{ $attributes->merge(['type' => 'text', 'name' => $name, 'data-slot' => 'questionnaire-input']) }}>
</div>
