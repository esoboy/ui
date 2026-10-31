@blaze(fold: true)

<li {{ $attributes->merge(['data-slot' => 'breadcrumb-item']) }}>{{ $slot }}</li>
