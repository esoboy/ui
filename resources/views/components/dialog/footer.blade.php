@blaze

@props([
    'showCloseButton' => false,
])

<div {{ $attributes->merge(['data-slot' => 'dialog-footer']) }}>
    {{ $slot }}
    @if ($showCloseButton)
        <x-ui::dialog.close><x-ui::button variant="outline">{{ __('ui::common.close') }}</x-ui::button></x-ui::dialog.close>
    @endif
</div>
