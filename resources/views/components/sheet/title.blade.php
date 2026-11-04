@blaze

@aware([
    'name',
])

<h2 {{ $attributes->merge(['id' => $name.'-title', 'data-slot' => 'sheet-title']) }}>{{ $slot }}</h2>
