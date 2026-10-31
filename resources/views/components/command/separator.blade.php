@blaze(fold: true)

{{-- Decoration between groups: a listbox holds only groups and options. --}}
<div {{ $attributes->merge(['role' => 'none', 'data-slot' => 'command-separator']) }}></div>
