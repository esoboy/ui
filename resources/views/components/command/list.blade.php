@blaze

{{-- The input controls it by an id its script gives; wire:key keeps morphs matching it without one. --}}
<div {{ $attributes->merge(['role' => 'listbox', 'aria-label' => __('ui::command.suggestions'), 'data-slot' => 'command-list', 'wire:key' => 'command-list']) }}>{{ $slot }}</div>
