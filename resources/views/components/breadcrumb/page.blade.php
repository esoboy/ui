@blaze(fold: true)

<span {{ $attributes->merge(['data-slot' => 'breadcrumb-page', 'role' => 'link', 'aria-disabled' => 'true', 'aria-current' => 'page']) }}>{{ $slot }}</span>
