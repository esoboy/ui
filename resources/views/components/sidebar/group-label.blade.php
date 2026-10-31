@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'sidebar-group-label', 'data-sidebar' => 'group-label']) }}>{{ $slot }}</div>
