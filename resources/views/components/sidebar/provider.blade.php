@blaze

@use('Esoboy\Ui\Support\SidebarState')

@props([
    'open' => true,
    'scope' => 'default',
])

<eso-sidebar-provider {{ $attributes->merge([
    'data-slot' => 'sidebar-wrapper',
    'style' => '--sidebar-width: 16rem; --sidebar-width-icon: 3rem;',
    'x-data' => 'uiSidebar('.json_encode(['open' => SidebarState::open($open, $scope), 'cookie' => SidebarState::cookie($scope)]).')',
    'x-on:click' => 'click($event)',
    'x-on:keydown.window' => 'shortcut($event)',
    'x-on:keydown.escape' => 'mobileOpen && showMobile(false)',
    'x-on:pointerdown.document' => 'outside($event)',
    'x-on:resize.window' => 'resize()',
    'x-on:livewire:navigating.document' => 'showMobile(false)',
]) }}>{{ $slot }}</eso-sidebar-provider>
