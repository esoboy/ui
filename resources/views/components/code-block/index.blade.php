@props(['code' => null, 'language' => null, 'filename' => null, 'variant' => 'default', 'size' => 'default', 'wrap' => false, 'lineNumbers' => false, 'startLine' => 1])
<figure {{ $attributes->class('group/code-block')->merge(['data-esoboy-ui' => '', 'data-slot' => 'code-block', 'data-variant' => $variant, 'data-size' => $size]) }} x-data="uiCode">
    @if ($code !== null)
    <x-ui::code-block.header>@isset($header){{ $header }}@else<x-ui::code-block.title>{{ $filename ?? $language ?? __('ui::ui.plain_text') }}</x-ui::code-block.title><x-ui::code-block.actions><x-ui::code-block.copy-button :value="$code" /></x-ui::code-block.actions>@endisset</x-ui::code-block.header>
    <x-ui::code-block.content :wrap="$wrap" :line-numbers="$lineNumbers" :start-line="$startLine">{{ $code }}</x-ui::code-block.content>
    @isset($footer)<x-ui::code-block.footer>{{ $footer }}</x-ui::code-block.footer>@endisset
    @endif
    {{ $slot }}
</figure>
