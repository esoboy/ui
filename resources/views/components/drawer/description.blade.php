@blaze

@aware([
    'name',
])

<p {{ $attributes->merge(['id' => $name.'-description', 'data-slot' => 'drawer-description']) }}>{{ $slot }}</p>
