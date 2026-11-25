@props(['value' => null, 'inputType' => 'auto'])
<div {{ $attributes->class('ui-rich-text ui-rich-content') }}>{!! \Esoboy\Ui\Support\RichContent::render($value ?? $slot->toHtml(), $inputType) !!}</div>
