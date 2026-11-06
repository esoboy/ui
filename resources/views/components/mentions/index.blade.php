@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- Not in shadcn (Ant Design's Mentions): a Textarea that suggests its items after a prefix, at the caret. wire:model, validation and class go to the textarea; mentions-search reports what is typed after the prefix. --}}
@props([
    'prefix' => '@',
    'split' => ' ',
    'filter' => null,
])

@php
// Validated here, as the textarea inside this view can't see the field around the mentions.
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$prefixes = array_values((array) $prefix);
$hasSearch = $attributes->whereStartsWith('wire:mentions-search')->isNotEmpty();
$remote = $hasSearch && Model::component() !== null;
$filter ??= ! $hasSearch;
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('mentions'.(Model::key($attributes) ?? ''), $loop ?? null);
@endphp

<eso-mentions x-data="uiMentions" data-slot="mentions" data-prefix="{{ json_encode($prefixes) }}" data-split="{{ $split }}" @if ($filter) data-local-catalogue @elseif ($remote) data-remote-catalogue @endif>
    <x-ui::textarea :attributes="$attributes->merge(['role' => 'combobox', 'aria-autocomplete' => 'list', 'aria-expanded' => 'false', 'data-mentions-input' => true, ...$state->attributes()])" />
    <div popover="manual" role="listbox" data-slot="mentions-content" wire:ignore.self>
        @if ($filter)
            @livewire('ui.mentions-results', ['markup' => (string) $slot, 'filter' => true, 'fallbackPrefix' => $prefixes[0] ?? '@'], key('ui-mentions-results-'.$key))
        @else
            @include('ui::livewire.mentions-results', ['content' => (string) $slot, 'empty' => $slot->isEmpty(), 'filter' => false])
        @endif
    </div>
    {{-- Where the caret is: the script copies the textarea's box and the text before the caret into it. --}}
    <div data-slot="mentions-mirror" aria-hidden="true"><span></span><span data-mentions-caret></span></div>
    <x-ui::field.error :state="$state" />
</eso-mentions>
<x-ui::extras />
