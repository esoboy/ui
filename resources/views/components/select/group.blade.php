@blaze(fold: true)

<div {{ $attributes->merge(['role' => 'group', 'data-slot' => 'select-group']) }}>{{ $slot }}</div>
