@blaze

@props([
    'defaultOpen' => true,
])

{{-- <eso-sidebar-provider> toggles every Sidebar inside it and remembers the state in the sidebar_state cookie. --}}
<eso-sidebar-provider {{ $attributes->merge(['data-slot' => 'sidebar-wrapper', 'style' => '--sidebar-width: 16rem; --sidebar-width-icon: 3rem;']) }}>{{ $slot }}</eso-sidebar-provider>
