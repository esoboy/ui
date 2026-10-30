@blaze

@use('Esoboy\Ui\Assets')

@props([
    'code' => null,
    'language' => null,
    'wrap' => false,
    'lineNumbers' => false,
    'startLine' => 1,
])

@php
// Given code, every line is a row (as numbers and highlighting need); otherwise the slot holds <x-ui::code-block.line> rows.
// An empty line holds a newline so copying a selection keeps blank lines.
$rows = $code === null ? null : implode('', array_map(
    fn (string $line) => '<span data-slot="code-block-line" data-variant="default"><span data-slot="code-block-line-content">'.($line === '' ? "\n" : e($line)).'</span></span>',
    explode("\n", preg_replace('/\n$/', '', str_replace("\r\n", "\n", $code))),
));
@endphp

<pre {{ $attributes->merge([
    'data-slot' => 'code-block-content',
    'data-language' => $language ?? false,
    'data-wrap' => (bool) $wrap,
    'data-numbered' => (bool) $lineNumbers,
    'dir' => 'ltr',
    'tabindex' => '0',
    'style' => 'counter-reset: line '.($startLine - 1).';',
]) }}><code data-slot="code-block-code">{!! $rows ?? $slot !!}</code></pre>

{{-- Livewire loads this once per page, also when the block arrives in a later update. --}}
@if ($language)
@assets
{!! app(Assets::class)->module('code-block.js') !!}
@endassets
@endif
