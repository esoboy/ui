@props(['src', 'alt' => '', 'initialValue' => null, 'disabled' => false, 'readonly' => false, 'crossorigin' => 'anonymous'])
@php($rect = \Esoboy\Ui\Support\DrawingValue::crop($initialValue))
<div {{ $attributes->whereDoesntStartWith(['wire:model', 'x-model'])->except(['name', 'value'])->class('ui-cropper ui-cropper-field') }}
    @disabled($disabled) @readonly($readonly)>
    <fieldset class="ui-cropper-body" x-data="uiCropper(@js($rect))" {{ $attributes->only('form') }} @disabled($disabled || $readonly)
        x-on:reset.window.capture="if ($event.target === $el.form) $nextTick(() => { if (!$event.defaultPrevented) reset() })">
        <div class="ui-cropper-surface" data-ui-crop-surface x-on:pointerdown.prevent="start($event)" x-on:pointermove="$root.disabled ? cancel($event) : move($event)"
            x-on:pointerup="$root.disabled ? cancel($event) : finish($event)" x-on:pointercancel="cancel($event)" x-on:lostpointercapture="cancel($event)">
            <x-ui::image :src="$src" :alt="$alt" :crossorigin="$crossorigin" draggable="false" />
            <div class="ui-cropper-frame" data-ui-crop-frame data-crop-mode="move" role="group" tabindex="{{ $disabled ? -1 : 0 }}" aria-label="{{ $attributes->get('aria-label', __('ui::ui.crop')) }}"
                x-bind:style="{ left: frame.x * 100 + '%', top: frame.y * 100 + '%', width: frame.width * 100 + '%', height: frame.height * 100 + '%' }"
                aria-disabled="{{ $disabled || $readonly ? 'true' : 'false' }}"
                style="left:{{ $rect['x'] * 100 }}%;top:{{ $rect['y'] * 100 }}%;width:{{ $rect['width'] * 100 }}%;height:{{ $rect['height'] * 100 }}%"
                x-on:keydown.left.prevent="nudge(-1, 0)" x-on:keydown.right.prevent="nudge(1, 0)"
                x-on:keydown.up.prevent="nudge(0, -1)" x-on:keydown.down.prevent="nudge(0, 1)"><span class="ui-cropper-resize" data-ui-crop-resize data-crop-mode="resize"></span></div>
        </div>
    </fieldset>
    {{ $slot }}
</div>
