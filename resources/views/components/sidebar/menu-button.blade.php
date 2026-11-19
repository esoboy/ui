@props(['size' => 'default', 'variant' => 'default', 'tooltip' => null, 'active' => false])
@if ($tooltip !== null)
    <x-ui::tooltip x-bind:data-disabled="Alpine.$data($el.closest('[data-slot=sidebar]')).opened || Alpine.$data($el.closest('[data-slot=sidebar]')).mobile ? 'true' : 'false'">
        <x-ui::tooltip.trigger><x-ui::sidebar.menu-button :size="$size" :variant="$variant" :active="$active" {{ $attributes }}>{{ $slot }}</x-ui::sidebar.menu-button></x-ui::tooltip.trigger>
        <x-ui::tooltip.content side="right">{{ $tooltip }}</x-ui::tooltip.content>
    </x-ui::tooltip>
@else
<button data-size="{{ $size }}" data-variant="{{ $variant }}" data-active="{{ $active ? 'true' : 'false' }}" type="button" {{ $attributes->class('ui-sidebar-menu-button group/menu-button peer/menu-button')->merge(['data-slot' => 'sidebar-menu-button']) }}>{{ $slot }}</button>
@endif
