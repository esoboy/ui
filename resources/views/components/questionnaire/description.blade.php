@blaze(fold: true)

{{-- wire:key: its script gives it an id, and Livewire's morph would otherwise match it by that id and replace it. --}}
<p {{ $attributes->merge(['data-slot' => 'questionnaire-description', 'wire:key' => 'questionnaire-description']) }}>{{ $slot }}</p>
