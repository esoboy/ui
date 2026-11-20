@blaze
@props(['value', 'selected' => false, 'disabled' => false])
<option value="{{ $value }}" @selected($selected) @disabled($disabled) {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'native-select-option']) }}>
    {{ $slot }}</option>
