@blaze

@aware([
    'name',
])

<h2 {{ $attributes->merge(['id' => $name.'-title', 'data-slot' => 'drawer-title']) }}>{{ $slot }}</h2>
