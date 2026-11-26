@aware(['tour' => null])
<span {{ $attributes->class('ui-tour-previous') }} wire:click.prevent="$dispatch('{{ $tour?->stepEvent() }}', { direction: -1 })">{{ $slot }}</span>
