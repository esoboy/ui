@props(['size' => 'md', 'active' => false])
<a {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'sidebar-menu-sub-button', 'data-size' => $size, 'data-active' => $active ? '' : null]) }}>{{ $slot }}</a>
