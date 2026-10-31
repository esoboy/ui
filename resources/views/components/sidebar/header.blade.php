@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'sidebar-header', 'data-sidebar' => 'header']) }}>{{ $slot }}</div>
