@blaze

@use('Esoboy\Ui\Support\CodeHighlight')

@props([
    'code' => null,
    'language' => null,
    'wrap' => false,
    'lineNumbers' => false,
    'startLine' => 1,
])

@php
$rows = $code === null ? null : CodeHighlight::lines(preg_replace('/\r?\n$/', '', $code) ?? $code, $language);
$compound = $rows === null && $language ? CodeHighlight::compound((string) $slot, $language) : $slot;
@endphp

<pre {{ $attributes->merge([
    'data-slot' => 'code-block-content',
    'data-language' => $language ?? false,
    'data-wrap' => (bool) $wrap,
    'data-numbered' => (bool) $lineNumbers,
    'dir' => 'ltr',
    'tabindex' => '0',
    'style' => 'counter-reset: line '.($startLine - 1).';',
]) }}><code data-slot="code-block-code">@if ($rows !== null)@foreach ($rows as $tokens)<x-ui::code-block.line :tokens="$tokens" />@endforeach@else{!! $compound !!}@endif</code></pre>
