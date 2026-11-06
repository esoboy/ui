@blaze

@aware([
    'name',
])

<p {{ $attributes->merge(['id' => $name.'-description', 'data-slot' => 'alert-dialog-description']) }}>{{ $slot }}</p>
