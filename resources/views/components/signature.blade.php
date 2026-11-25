@props(['initialValue' => null, 'disabled' => false, 'readonly' => false])
@php($strokes = \Esoboy\Ui\Support\DrawingValue::signature($initialValue))
<div {{ $attributes->whereDoesntStartWith(['wire:model', 'x-model'])->except(['name', 'value'])->class('ui-signature ui-signature-field') }}
    @disabled($disabled) @readonly($readonly)>
    <fieldset class="ui-signature-body" x-data="uiSignature(@js($strokes), {{ \Esoboy\Ui\Support\DrawingValue::MAX_STROKES }}, {{ \Esoboy\Ui\Support\DrawingValue::MAX_POINTS }})"
        x-on:reset.window.capture="if ($event.target === $el.form) $nextTick(() => { if (!$event.defaultPrevented) reset() })"
        {{ $attributes->only('form') }} @disabled($disabled || $readonly)>
        <div class="ui-signature-surface" data-ui-signature-surface aria-disabled="{{ $disabled || $readonly ? 'true' : 'false' }}">
            <canvas wire:ignore aria-label="{{ $attributes->get('aria-label', __('ui::ui.signature')) }}"
                x-on:pointerdown.prevent="start($event)" x-on:pointermove.prevent="$root.disabled ? cancel($event) : move($event)"
                x-on:pointerup="$root.disabled ? cancel($event) : finish($event)" x-on:pointercancel="cancel($event)" x-on:lostpointercapture="cancel($event)"></canvas>
        </div>
        <x-ui::button variant="outline" x-on:click="reset([])">{{ __('ui::ui.clear') }}</x-ui::button>
    </fieldset>
    {{ $slot }}
</div>
