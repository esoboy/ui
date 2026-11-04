@blaze

@props([
    'showIcon' => false,
])

<div {{ $attributes->merge(['data-slot' => 'sidebar-menu-skeleton', 'data-sidebar' => 'menu-skeleton']) }}>
    @if ($showIcon)
    <x-ui::skeleton class="size-4 rounded-md" data-sidebar="menu-skeleton-icon" />
    @endif
    {{-- A random width between 50% and 90%, like shadcn's. --}}
    <x-ui::skeleton class="h-4 max-w-(--skeleton-width) flex-1" data-sidebar="menu-skeleton-text" :style="'--skeleton-width: '.random_int(50, 89).'%'" />
</div>
