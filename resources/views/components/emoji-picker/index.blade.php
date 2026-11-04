@blaze

@use('Esoboy\Ui\Assets')

{{-- Not in shadcn's core: its registry's Emoji Picker (Liveblocks' Frimousse). The emojis come from Emojibase in the app's language when the build has it, else English, loaded when the picker first shows; picking one dispatches emoji-select { emoji, label }. --}}
@props([
    'columns' => 9,
])

@php
$assets = app(Assets::class);
$locale = strtolower(str_replace('_', '-', app()->getLocale()));
$data = collect(["emoji/{$locale}.json", 'emoji/'.strtok($locale, '-').'.json', 'emoji/en.json'])->first(fn (string $file) => $assets->path($file) !== null, 'emoji/en.json');
@endphp

<eso-emoji-picker {{ $attributes->merge([
    'data-slot' => 'emoji-picker',
    'data-src' => $assets->url($data),
    'data-columns' => $columns,
    'style' => "--emoji-picker-columns: {$columns};",
]) }}>{{ $slot }}</eso-emoji-picker>
<x-ui::extras />
