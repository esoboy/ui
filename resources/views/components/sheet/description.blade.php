@blaze

@aware([
    'name',
])

<p {{ $attributes->merge(['id' => $name.'-description', 'data-slot' => 'sheet-description']) }}>{{ $slot }}</p>
