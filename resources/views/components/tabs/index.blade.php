@blaze

@use('Esoboy\Ui\Support\Model')

@props([
    'value' => null,
    'orientation' => 'horizontal',
    'uiModel' => null,
    'uiValue' => null,
])

@php
$value = Model::value($attributes, $value);
if ($value === null && preg_match('/data-slot="tabs-trigger"[^>]*data-value="([^"]+)"/', (string) $slot, $first)) {
    $value = html_entity_decode($first[1], ENT_QUOTES, 'UTF-8');
}
\Esoboy\Ui\Support\Sequence::reset('tabs-trigger');
\Esoboy\Ui\Support\Sequence::reset('tabs-content');
@endphp

<eso-tabs {{ $attributes->whereDoesntStartWith('wire:model')->except('name')->merge(['data-slot' => 'tabs', 'data-orientation' => $orientation, 'data-value' => is_scalar($value) ? (string) $value : false, 'x-data' => 'uiTabs('.json_encode(Model::binding($attributes)).')', 'x-effect' => 'refresh()', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)', 'x-on:focusout' => '!$el.contains($event.relatedTarget) && blur()']) }}>
    {{ $slot }}
    <input type="hidden" x-ref="input" value="{{ $value }}" {{ $attributes->whereStartsWith('wire:model')->merge($attributes->only('name')->getAttributes()) }}>
</eso-tabs>
