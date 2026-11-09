@blaze

@aware([
    'name',
])

<p {{ $attributes->merge(['id' => $name.'-description', 'data-slot' => 'dialog-description']) }}>{{ $slot }}</p>
