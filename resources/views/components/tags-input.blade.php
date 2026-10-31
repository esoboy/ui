@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- Enter or a comma adds what was typed, Backspace in the empty input removes the last tag. --}}
@props([
    'placeholder' => null,
    'max' => null,
    'disabled' => false,
    'defaultValue' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$tags = array_values(array_map('strval', (array) ($defaultValue ?? Model::value($attributes) ?? [])));
$name = $attributes->get('name');
@endphp

{{-- <eso-tags-input> exposes the tags as .value, so wire:model binds on it. --}}
<eso-tags-input {{ $attributes->except('name')->merge([
    'data-slot' => 'tags-input',
    'data-value' => json_encode($tags),
    'data-max' => $max ?? false,
    'data-name' => $name ?? false,
    'data-disabled' => (bool) $disabled,
]) }}>
    <template data-tags-input-template>
        <span data-slot="tags-input-tag">
            <span data-tags-input-label></span>
            <x-ui::button variant="ghost" size="icon-xs" data-tags-input-remove :aria-label="__('ui::common.remove')" tabindex="-1">
                <x-ui::icon name="x" />
            </x-ui::button>
        </span>
    </template>
    @foreach ($tags as $tag)
    <span data-slot="tags-input-tag" wire:key="tag-{{ $loop->index }}"><span data-tags-input-label>{{ $tag }}</span></span>
    @endforeach
    <input type="text" data-slot="tags-input-input" placeholder="{{ $placeholder }}" autocomplete="off" {{ new \Illuminate\View\ComponentAttributeBag($state->attributes()) }} @if ($disabled) disabled @endif>
    @if ($name)
        @foreach ($tags as $tag)
        <input type="hidden" name="{{ $name }}[]" value="{{ $tag }}">
        @endforeach
    @endif
</eso-tags-input>
<x-ui::field.error :state="$state" />
<x-ui::extras />
