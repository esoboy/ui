@blaze

@aware([
    'name',
    'multiple' => false,
])

{{-- The same elements as Radio Group / Checkbox Group: wire:model binds here. --}}
@if ($multiple)
<eso-checkbox-group {{ $attributes->merge(['role' => 'group', 'name' => $name.'[]', 'data-slot' => 'questionnaire-choices']) }}>{{ $slot }}</eso-checkbox-group>
@else
<eso-radio-group {{ $attributes->merge(['role' => 'radiogroup', 'name' => $name, 'data-slot' => 'questionnaire-choices']) }}>{{ $slot }}</eso-radio-group>
@endif
