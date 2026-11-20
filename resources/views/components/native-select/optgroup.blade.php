@blaze
@props(['label'])
<optgroup label="{{ $label }}" {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'native-select-optgroup']) }}>{{ $slot }}</optgroup>
