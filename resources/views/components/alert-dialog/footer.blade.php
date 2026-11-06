@blaze(fold: true)

<div {{ $attributes->merge(['data-slot' => 'alert-dialog-footer']) }}>{{ $slot }}</div>
