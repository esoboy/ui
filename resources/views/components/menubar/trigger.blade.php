@blaze(fold: true)

{{-- Opens the Menubar Content after it; commandfor stays empty, ui.js points it there. --}}
<button {{ $attributes->merge([
    'type' => 'button',
    'role' => 'menuitem',
    'aria-haspopup' => 'menu',
    'data-slot' => 'menubar-trigger',
    'command' => 'toggle-popover',
    'commandfor' => '',
]) }}>{{ $slot }}</button>
