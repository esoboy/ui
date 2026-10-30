@blaze(fold: true)

{{-- Right-clicking the area opens the Context Menu Content after it at the pointer. --}}
<div {{ $attributes->merge(['data-slot' => 'context-menu-trigger']) }}>{{ $slot }}</div>
