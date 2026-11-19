<div role="menubar" {{ $attributes->merge(['data-slot' => 'menubar']) }} x-data="uiMenubar" x-on:keydown="navigate($event)">{{ $slot }}</div>
