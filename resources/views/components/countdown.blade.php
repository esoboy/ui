@blaze

@use('Esoboy\Ui\Support\Countdown')
@use('Esoboy\Ui\Support\Model')

@props(['until' => null, 'seconds' => null, 'name' => null, 'format' => 'HH:mm:ss'])

<eso-countdown {{ $attributes->merge(['role' => 'timer', 'data-slot' => 'countdown']) }}>
    @livewire('ui.countdown', [
        'remaining' => Countdown::remaining($until, $seconds),
        'seconds' => (int) ($seconds ?? 60), 'name' => $name, 'format' => $format,
    ], key(Model::scope('countdown', $loop ?? null)))
</eso-countdown>
<x-ui::extras />
