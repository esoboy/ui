@php
    $name = $attributes->get('name');
    $disabled = $settings['disabled'] ?? false;
    $validation = $settings['validation'] ?? [];
    $popup = $this->getId().'-listbox';
@endphp
<eso-select {{ $attributes->whereDoesntStartWith('wire:model')->except('name')->merge([
    'data-slot' => 'select',
    'data-value' => is_scalar($value) ? (string) $value : false,
    'data-name' => $name,
    'x-data' => 'uiSelect',
    'x-on:ui-model-changed.self' => 'notifyModel()',
    'x-on:focusout' => '!$el.contains($event.relatedTarget) && blurModel()',
    'x-on:click' => 'click($event)',
    'x-on:keydown' => 'key($event)',
    'x-on:toggle.capture' => 'toggle($event)',
    'x-on:scroll.document.capture.passive' => 'align($event)',
    'x-on:resize.window' => 'align()',
    ...$validation,
]) }}>
    @if ($choiceMarkup)
        {!! $choiceMarkup->render($value, $validation, $disabled, $popup) !!}
    @else
        <x-ui::select.trigger :size="$settings['size'] ?? 'default'" :disabled="$disabled" :commandfor="$popup" :aria-controls="$popup" :data-placeholder="$label === null" :attributes="new \Illuminate\View\ComponentAttributeBag($validation)">
            <x-ui::select.value :placeholder="$settings['placeholder'] ?? null" :label="$label" />
        </x-ui::select.trigger>
        <x-ui::select.content :id="$popup">
            <x-ui::select.group>
                @foreach ($choices as $option)
                    <x-ui::select.item :value="$option['value']" :disabled="! empty($option['disabled'])" :aria-selected="$value !== null && (string) $value === $option['value'] ? 'true' : 'false'">{{ $option['label'] }}</x-ui::select.item>
                @endforeach
            </x-ui::select.group>
        </x-ui::select.content>
    @endif
    <select hidden aria-hidden="true" tabindex="-1" @if ($name) name="{{ $name }}" @endif @disabled($disabled)>
        <option value=""></option>
        @foreach ($choices as $option)
            <option value="{{ $option['value'] }}" @selected((string) $value === $option['value']) @disabled(! empty($option['disabled']))>{{ $option['label'] }}</option>
        @endforeach
    </select>
</eso-select>
