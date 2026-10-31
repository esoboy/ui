@blaze(fold: true)

<div {{ $attributes->merge(['role' => 'group', 'data-slot' => 'dropdown-menu-group']) }}>{{ $slot }}</div>
