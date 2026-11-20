@props(['selected' => false])
@aware(['type' => 'single', 'variant' => 'default', 'size' => 'default', 'disabled' => false])
<label data-esoboy-ui data-slot="toggle-group-item" data-variant="{{ $variant }}" data-size="{{ $size }}"><input type="{{ $type === 'multiple' ? 'checkbox' : 'radio' }}" {{ $attributes->except(['class', 'disabled']) }} @checked($selected)
        @disabled($disabled || $attributes->get('disabled')) class="ui-visually-hidden"><span class="ui-toggle {{ $attributes->get('class') }}" data-variant="{{ $variant }}"
        data-size="{{ $size }}">{{ $slot }}</span></label>
