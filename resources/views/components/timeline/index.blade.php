@blaze(fold: true)

<ol {{ $attributes->merge(['data-slot' => 'timeline']) }}>{{ $slot }}</ol>
