@blaze

{{-- The active emoji (hovered, or moved to with the arrow keys) and its label, else a prompt. --}}
<div {{ $attributes->merge(['data-slot' => 'emoji-picker-footer']) }} wire:ignore>
    <div data-slot="emoji-picker-active-emoji" hidden></div>
    <span data-slot="emoji-picker-active-label" hidden></span>
    <span data-slot="emoji-picker-placeholder">{{ __('ui::emoji-picker.placeholder') }}</span>
</div>
