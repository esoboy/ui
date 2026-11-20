@props(['direction' => 'end'])
<span {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'message-scroller-button', 'data-direction' => $direction]) }} data-ui-slot-error="{{ __('ui::errors.navigation_slot') }}" x-ui-step="{{ $direction === 'start' ? '-1' : '1' }}" x-show="{{ $direction === 'start' ? '!atStart' : '!anchored' }}" x-cloak>{{ $slot }}</span>
