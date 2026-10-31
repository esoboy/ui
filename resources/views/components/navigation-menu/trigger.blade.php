@blaze(fold: true)

{{-- Opens the Navigation Menu Content after it, on hover or click (ui.js). --}}
<button {{ $attributes->merge([
    'type' => 'button',
    'aria-expanded' => 'false',
    'data-slot' => 'navigation-menu-trigger',
]) }}>
    {{ $slot }}
    <x-ui::icon name="chevron-down" data-slot="navigation-menu-trigger-icon" />
</button>
