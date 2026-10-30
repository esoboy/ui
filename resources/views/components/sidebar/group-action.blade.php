@blaze(fold: true)

<button {{ $attributes->merge(['type' => 'button', 'data-slot' => 'sidebar-group-action', 'data-sidebar' => 'group-action']) }}>{{ $slot }}</button>
