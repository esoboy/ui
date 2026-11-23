@blaze
@props(['align' => 'inline-start'])
<div data-align="{{ $align }}" x-init="" x-on:click="if (!$event.target.closest('button,a,input,textarea,select')) $el.parentElement.querySelector('.ui-input-group-control')?.focus()"
    {{ $attributes->merge(['class' => 'ui-input-group-addon', 'role' => 'group']) }}>{{ $slot }}</div>
