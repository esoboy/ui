<div {{ (new \Illuminate\View\ComponentAttributeBag($footerAttributes))->merge(['data-slot' => 'emoji-picker-footer']) }}
    wire:emoji-preview="preview($event.detail.glyph, $event.detail.label)">
    @if ($glyph !== '')
        <div data-slot="emoji-picker-active-emoji">{{ $glyph }}</div>
        <span data-slot="emoji-picker-active-label">{{ $label }}</span>
    @else
        <span data-slot="emoji-picker-placeholder">{{ __('ui::emoji-picker.placeholder') }}</span>
    @endif
</div>
