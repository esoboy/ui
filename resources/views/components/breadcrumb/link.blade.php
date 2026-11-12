@blaze(fold: true, unsafe: ['href'])

<a {{ $attributes->merge(['data-slot' => 'breadcrumb-link']) }}>{{ $slot }}</a>
