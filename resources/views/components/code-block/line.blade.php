@props(['variant' => 'default'])
<span {{ $attributes->class('ui-code-block-line ui-code-block-line-variant-'.$variant) }}><span class="ui-code-block-line-content">{{ $slot->isEmpty() ? "\n" : $slot }}</span></span>
