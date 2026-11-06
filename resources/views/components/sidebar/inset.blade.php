@blaze(fold: true)

<main {{ $attributes->merge(['data-slot' => 'sidebar-inset']) }}>{{ $slot }}</main>
