@blaze

@use('Esoboy\Ui\Support\Model')

@props(['name' => 'verification-code', 'seconds' => 60, 'variant' => 'outline', 'size' => 'default'])

<eso-verification-code-button data-slot="verification-code-button" x-data="{ running: false, started: false }"
    x-on:ui-countdown-state="running = $event.detail.running; started = $event.detail.started"
    x-on:click.capture="if (running) { $event.preventDefault(); $event.stopImmediatePropagation() }">
    <x-ui::button :variant="$variant" :size="$size" :attributes="$attributes->merge(['x-bind:aria-disabled' => 'running || null'])">
        <span data-verification-code-label x-show="!started">{{ $slot }}</span>
        @livewire('ui.countdown', [
            'verification' => true, 'name' => $name, 'seconds' => (int) $seconds,
        ], key(Model::scope('verification-code', $loop ?? null)))
    </x-ui::button>
</eso-verification-code-button>
<x-ui::extras />
