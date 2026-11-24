@props(['size' => 'default'])
<div role="radiogroup" {{ $attributes->class('ui-radio-group')->merge(['data-size' => $size]) }}>{{ $slot }}</div>
