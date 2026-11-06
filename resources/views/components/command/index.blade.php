@blaze

@props([
    'filter' => true,
])

@if ($filter)
    @php
        $owner = \Esoboy\Ui\Support\Model::component();
        $key = $attributes->get('wire:key') ?? $attributes->get('id') ?? \Esoboy\Ui\Support\Model::scope('command', $loop ?? null);
        $input = $owner ? \Esoboy\Ui\Support\HtmlFragment::parse((string) $slot)->querySelector('[data-command-input]') : null;
        $binding = [];
        foreach ($input?->attributes ?? [] as $attribute) {
            if (explode('.', $attribute->name)[0] === 'wire:model') {
                $binding[$attribute->name] = $attribute->value;
            }
        }
        $boundQuery = $binding ? (string) \Esoboy\Ui\Support\Model::value(new \Illuminate\View\ComponentAttributeBag($binding)) : null;
    @endphp
    @livewire('ui.command', ['markup' => (string) $slot, 'hasParent' => $owner !== null, 'boundQuery' => $boundQuery, ...$attributes->except('wire:key')->getAttributes()], key('ui-command-'.$key))
@else
    <eso-command {{ $attributes->merge(['data-slot' => 'command', 'data-filter' => 'false', 'data-empty' => ! str_contains((string) $slot, 'data-slot="command-item"'), 'x-data' => 'uiCommand', 'x-on:keydown' => 'key($event)', 'x-on:pointermove' => 'hover($event)']) }}>{{ $slot }}</eso-command>
@endif
