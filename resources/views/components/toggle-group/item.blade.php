@props(['selected' => false])
@aware(['type' => 'single', 'variant' => 'default', 'size' => 'default', 'disabled' => false])
<label class="ui-toggle-group-item ui-toggle-group-item-variant-{{ $variant }} {{ $attributes->get('class') }}" data-size="{{ $size }}"><input type="{{ $type === 'multiple' ? 'checkbox' : 'radio' }}" {{ $attributes->except(['class', 'disabled']) }} @checked($selected)
        @disabled($disabled || $attributes->get('disabled')) class="ui-visually-hidden"><span class="ui-toggle-control ui-toggle-control-variant-{{ $variant }}"
        data-size="{{ $size }}">{{ $slot }}</span></label>
