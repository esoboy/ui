@blaze

{{-- A Button that counts down once the server sends the code and calls Ui::countdown($name). --}}
@props([
    'name' => 'verification-code',
    'seconds' => 60,
    'variant' => 'outline',
    'size' => 'default',
])

<eso-verification-code-button data-slot="verification-code-button" data-name="{{ $name }}" data-seconds="{{ $seconds }}" data-label-countdown="{{ __('ui::verification-code-button.countdown') }}" data-label-resend="{{ __('ui::verification-code-button.resend') }}">
    <x-ui::button :variant="$variant" :size="$size" :attributes="$attributes">
        <span data-verification-code-label>{{ $slot }}</span>
    </x-ui::button>
</eso-verification-code-button>
<x-ui::extras />
