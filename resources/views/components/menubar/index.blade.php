@blaze(fold: true)

<div {{ $attributes->merge(['role' => 'menubar', 'data-slot' => 'menubar']) }}>{{ $slot }}</div>
