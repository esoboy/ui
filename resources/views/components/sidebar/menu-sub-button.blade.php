@props(['size' => 'md', 'active' => false])
<a {{ $attributes->merge(['class' => 'ui-sidebar-menu-sub-button', 'data-size' => $size, 'data-active' => $active ? '' : null]) }}>{{ $slot }}</a>
