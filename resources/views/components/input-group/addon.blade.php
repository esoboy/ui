@blaze
@props(['align' => 'inline-start'])
<div data-align="{{ $align }}" x-init="" x-on:click="if (!$event.target.closest('button,a,input,textarea,select')) $el.parentElement.querySelector('[data-slot=input-group-control]')?.focus()"
    {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'input-group-addon', 'role' => 'group']) }}>{{ $slot }}</div>
