@blaze

@aware([
    'required' => false,
])

{{-- Shown when an item can't move on; the text defaults to what the item needs. --}}
{{-- wire:key: its script gives it an id, and Livewire's morph would otherwise match it by that id and replace it. --}}
<p {{ $attributes->merge(['data-slot' => 'questionnaire-error', 'wire:key' => 'questionnaire-error', 'hidden' => true]) }}>@if ($slot->isEmpty()){{ $required ? __('ui::questionnaire.required') : __('ui::questionnaire.optional') }}@else{{ $slot }}@endif</p>
