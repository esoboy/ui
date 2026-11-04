@blaze(fold: true)

{{-- Floats its buttons (Float Buttons, Tooltip Triggers, Back to Top) at the window's bottom end corner, one above the other. --}}
<div {{ $attributes->merge(['data-slot' => 'float-button-group']) }}>{{ $slot }}</div>
