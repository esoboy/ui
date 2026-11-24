@props(['size' => 'default', 'variant' => 'default', 'tooltip' => null, 'active' => false])
@if ($tooltip !== null)
    @component('ui::partials.sidebar-tooltip', ['tooltip' => $tooltip])
        <x-ui::sidebar.menu-button :size="$size" :variant="$variant" :active="$active" {{ $attributes }}>{{ $slot }}</x-ui::sidebar.menu-button>
    @endcomponent
@else
<button data-size="{{ $size }}" data-active="{{ $active ? 'true' : 'false' }}" type="button" {{ $attributes->class(['ui-sidebar-menu-button group/menu-button peer/menu-button', 'ui-sidebar-menu-button-variant-'.$variant]) }}>{{ $slot }}</button>
@endif
