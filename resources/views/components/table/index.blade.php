@blaze
@props(['size' => 'default'])
<div {{ $attributes->only('class')->class('ui-table ui-table-container')->merge(['data-size' => $size]) }}><table {{ $attributes->except('class')->class('ui-table-element') }}>{{ $slot }}</table></div>
