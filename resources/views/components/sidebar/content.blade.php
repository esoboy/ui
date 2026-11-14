@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'sidebar-content', 'data-sidebar' => 'content']) }}>{{ $slot }}</div>
