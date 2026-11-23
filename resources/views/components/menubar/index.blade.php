<div role="menubar" {{ $attributes->class('ui-menubar') }} x-data="uiMenubar" x-on:keydown="navigate($event)">{{ $slot }}</div>
