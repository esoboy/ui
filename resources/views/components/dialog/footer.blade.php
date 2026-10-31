@blaze

@props([
    'showCloseButton' => false,
])

<div {{ $attributes->merge(['data-slot' => 'dialog-footer']) }}>
    {{ $slot }}
    @if ($showCloseButton)
        <x-ui::dialog.close variant="outline">{{ __('ui::common.close') }}</x-ui::dialog.close>
    @endif
</div>
