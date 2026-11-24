@props(['active' => false, 'size' => 'default', 'variant' => 'default', 'tooltip' => null])
@if ($tooltip !== null)
    @component('ui::partials.sidebar-tooltip', ['tooltip' => $tooltip])
        <x-ui::sidebar.menu-link :size="$size" :variant="$variant" :active="$active" {{ $attributes }}>{{ $slot }}</x-ui::sidebar.menu-link>
    @endcomponent
@else
<a data-size="{{ $size }}" data-active="{{ $active ? 'true' : 'false' }}" @if($active) aria-current="page" @endif {{ $attributes->class(['ui-sidebar-menu-button group/menu-button peer/menu-button ui-sidebar-menu-link', 'ui-sidebar-menu-button-variant-'.$variant]) }}>{{ $slot }}</a>
@endif
