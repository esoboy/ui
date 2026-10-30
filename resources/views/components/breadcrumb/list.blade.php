@blaze(fold: true)

<ol {{ $attributes->merge(['data-slot' => 'breadcrumb-list']) }}>{{ $slot }}</ol>
