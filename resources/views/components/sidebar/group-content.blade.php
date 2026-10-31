@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'sidebar-group-content', 'data-sidebar' => 'group-content']) }}>{{ $slot }}</div>
