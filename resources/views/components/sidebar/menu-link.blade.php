@props(['active' => false, 'size' => 'default', 'variant' => 'default', 'tooltip' => null])
@if ($tooltip !== null)
    <x-ui::tooltip x-bind:data-disabled="Alpine.$data($el.closest('[data-slot=sidebar]')).opened || Alpine.$data($el.closest('[data-slot=sidebar]')).mobile ? 'true' : 'false'">
        <x-ui::tooltip.trigger><x-ui::sidebar.menu-link :size="$size" :variant="$variant" :active="$active" {{ $attributes }}>{{ $slot }}</x-ui::sidebar.menu-link></x-ui::tooltip.trigger>
        <x-ui::tooltip.content side="right">{{ $tooltip }}</x-ui::tooltip.content>
    </x-ui::tooltip>
@else
<a data-size="{{ $size }}" data-variant="{{ $variant }}" data-active="{{ $active ? 'true' : 'false' }}" @if($active) aria-current="page" @endif {{ $attributes->class('ui-sidebar-menu-button group/menu-button peer/menu-button')->merge(['data-esoboy-ui' => '', 'data-slot' => 'sidebar-menu-link']) }}>{{ $slot }}</a>
@endif
