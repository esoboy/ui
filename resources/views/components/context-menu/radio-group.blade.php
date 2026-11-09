@blaze
@props(['value' => null, 'uiModel' => null, 'uiValue' => null, 'uiName' => null])
<div {{ $attributes->whereDoesntStartWith('wire:model')->except('name')->merge(['role' => 'group', 'data-slot' => 'dropdown-menu-radio-group']) }}>{{ $slot }}</div>
