@blaze
@props(['label'])
<optgroup label="{{ $label }}" {{ $attributes->merge(['data-slot' => 'native-select-optgroup']) }}>{{ $slot }}</optgroup>
