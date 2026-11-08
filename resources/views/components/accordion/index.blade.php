@blaze

@props([
    'value' => null,
    'multiple' => false,
    'name' => null,
    'uiValue' => null,
    'uiModel' => null,
    'uiName' => null,
])

@php
$name ??= 'ui-accordion-'.\Esoboy\Ui\Support\Sequence::next('accordion');
$content = (string) $slot;
if (! $multiple) {
    $content = preg_replace('/<details\b(?![^>]*\bname=)/', '<details name="'.e($name).'"', $content) ?? $content;
}
@endphp

<eso-accordion {{ $attributes->whereDoesntStartWith('wire:model')->merge(['data-slot' => 'accordion', 'data-multiple' => $multiple, 'x-data' => 'uiAccordion('.json_encode(\Esoboy\Ui\Support\Model::binding($attributes)).', '.json_encode((bool) $multiple).')', 'x-effect' => 'sync()', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)', 'x-on:click.capture' => '$event.target.closest(\'summary[aria-disabled="true"]\') && $event.preventDefault()']) }}>{!! $content !!}</eso-accordion>
