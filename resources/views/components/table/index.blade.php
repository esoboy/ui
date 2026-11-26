@blaze
@props(['size' => 'default'])
<div {{ $attributes->only(['class', 'wire:key'])->class('ui-table ui-table-container')->merge(['data-size' => $size]) }}><table {{ $attributes->except(['class', 'wire:key'])->class('ui-table-element') }}>{{ $slot }}</table></div>
