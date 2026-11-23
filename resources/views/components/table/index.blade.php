@blaze
<div {{ $attributes->only('class')->class('ui-table ui-table-container') }}><table {{ $attributes->except('class')->class('ui-table-element') }}>{{ $slot }}</table></div>
