<span x-data="uiCountdown" x-on:ui-countdown.window="start($event.detail)"
    data-countdown-clock data-name="{{ $name }}" data-seconds="{{ $seconds }}"
    data-deadline="{{ $deadline }}" data-delay="{{ $delay }}"
    data-running="{{ $remaining > 0 ? 'true' : 'false' }}" data-started="{{ $deadline > 0 ? 'true' : 'false' }}"
    @if ($verification && ! $deadline) hidden @endif>
    @if (! $verification)
        {{ \Esoboy\Ui\Support\Countdown::format($remaining, $format) }}
    @elseif ($remaining > 0)
        {{ __('ui::verification-code-button.countdown', ['seconds' => (int) ceil($remaining / 1000)]) }}
    @elseif ($deadline)
        {{ __('ui::verification-code-button.resend') }}
    @endif
</span>
