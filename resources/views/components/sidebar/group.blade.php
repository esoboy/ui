@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'sidebar-group', 'data-sidebar' => 'group']) }}>{{ $slot }}</div>
