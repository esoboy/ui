@blaze
@props(['label'])
<optgroup label="{{ $label }}" {{ $attributes->class('ui-native-select-optgroup') }}>{{ $slot }}</optgroup>
