@blaze

<button {{ $attributes->merge([
    'type' => 'button',
    'data-sidebar' => 'rail',
    'data-slot' => 'sidebar-rail',
    'aria-label' => __('ui::sidebar.toggle'),
    'title' => __('ui::sidebar.toggle'),
    'tabindex' => '-1',
]) }}></button>
