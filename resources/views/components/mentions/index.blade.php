@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- Not in shadcn (Ant Design's Mentions): a Textarea that suggests its items after a prefix, at the caret. wire:model, validation and class go to the textarea; mentions-search reports what is typed after the prefix. --}}
@props([
    'prefix' => '@',
    'split' => ' ',
])

@php
// Validated here, as the textarea inside this view can't see the field around the mentions.
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
@endphp

<eso-mentions data-slot="mentions" data-prefix="{{ json_encode(array_values((array) $prefix)) }}" data-split="{{ $split }}">
    <x-ui::textarea :attributes="$attributes->merge(['role' => 'combobox', 'aria-autocomplete' => 'list', 'aria-expanded' => 'false', 'data-mentions-input' => true, ...$state->attributes()])" />
    <div popover="manual" role="listbox" data-slot="mentions-content" wire:ignore.self>
        <div data-slot="mentions-list">{{ $slot }}</div>
        <div data-slot="mentions-empty">{{ __('ui::mentions.empty') }}</div>
    </div>
    {{-- Where the caret is: the script copies the textarea's box and the text before the caret into it. --}}
    <div data-slot="mentions-mirror" aria-hidden="true"><span></span><span data-mentions-caret></span></div>
    <x-ui::field.error :state="$state" />
</eso-mentions>
<x-ui::extras />
