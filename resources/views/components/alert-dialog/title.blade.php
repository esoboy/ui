@blaze

@aware([
    'name',
])

<h2 {{ $attributes->merge(['id' => $name.'-title', 'data-slot' => 'alert-dialog-title']) }}>{{ $slot }}</h2>
