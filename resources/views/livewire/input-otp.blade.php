<eso-input-otp {{ $attributes->only(['class', 'style'])->merge([
    'data-slot' => 'input-otp',
    'x-data' => 'uiOtp',
    'x-effect' => '$wire.value; render()',
    'x-on:input' => 'render()',
    'x-on:focusin' => 'render()',
    'x-on:focusout' => 'render()',
    'x-on:select' => 'render()',
    'x-on:keyup' => 'render()',
    'x-on:click' => 'render()',
    'x-on:selectionchange.document' => 'render()',
]) }}>
    {!! $cells !!}
    <input {{ $attributes->whereDoesntStartWith('wire:model')->except(['class', 'style', 'value'])->merge([
        'type' => 'text',
        'data-slot' => 'input-otp-input',
        'maxlength' => $settings['maxlength'] ?? 6,
        'autocomplete' => 'one-time-code',
        'spellcheck' => 'false',
        'value' => $code,
        ...($settings['validation'] ?? []),
    ]) }} wire:model.live.debounce.100ms="value">
</eso-input-otp>
