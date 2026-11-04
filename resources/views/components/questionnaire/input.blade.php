@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'name',
])

{{-- A freeform answer; give it a visible label or aria-label. --}}
<div data-slot="questionnaire-input-wrapper">
    {{-- The questionnaire owns required-answer validation and its error message. --}}
    <x-ui::input :state="ControlState::for($attributes, null, false)" :attributes="$attributes->merge(['name' => $name, 'data-questionnaire-input' => true, 'class' => 'min-h-11 sm:min-h-0'])" />
</div>
