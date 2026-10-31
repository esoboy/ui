@blaze(fold: true, unsafe: ['slot'])

<div {{ $attributes->merge(['data-slot' => 'field-separator', 'data-content' => $slot->hasActualContent() ? 'true' : 'false']) }}>
    <x-ui::separator />
    @if ($slot->hasActualContent())
        <span data-slot="field-separator-content">{{ $slot }}</span>
    @endif
</div>
