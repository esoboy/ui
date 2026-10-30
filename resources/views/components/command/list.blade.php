@blaze(fold: true)

<div {{ $attributes->merge(['role' => 'listbox', 'data-slot' => 'command-list']) }}>{{ $slot }}</div>
