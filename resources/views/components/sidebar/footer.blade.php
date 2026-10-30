@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'sidebar-footer', 'data-sidebar' => 'footer']) }}>{{ $slot }}</div>
