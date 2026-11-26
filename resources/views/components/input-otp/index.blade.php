@props(['size' => 'default', 'length' => 6, 'pattern' => 'numeric', 'separatorAfter' => 0, 'cellClass' => '', 'groupClass' => '', 'value', 'initialValue'])
@php
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'input-otp', get_defined_vars(), ['length' => $length, 'pattern' => $pattern, 'separatorAfter' => $separatorAfter]);
    $disabled = $binding->config['disabled'];
    $readonly = $binding->config['readonly'];
    $fieldAttributes = \Esoboy\Ui\Support\ControlAttributes::forInput($attributes, ['required', 'form', 'tabindex', 'enterkeyhint']);
    $breaks = is_array($separatorAfter) ? $separatorAfter : ($separatorAfter ? [$separatorAfter] : []);
    $groups = [];
    $start = 0;
    foreach ([...$breaks, $length] as $end) {
        $groups[] = range($start, $end - 1);
        $start = $end;
    }
@endphp
<{{ $binding->containerTag }} {{ $binding->loading }} {{ $attributes->only(['class', 'wire:key'])->class('ui-input-otp ui-input-otp-field')->merge(['data-size' => $size]) }}>
    <div x-data="uiOtp()"
        x-effect="@if($binding->config['mode'] === 'bound') $wire.$get(@js($binding->config['model'])); @endif $nextTick(() => refresh(@js([$length, $pattern, $binding->config['mode'] === 'controlled' ? $binding->config['value'] : null])))" data-ui-control="{{ json_encode($binding->config, JSON_THROW_ON_ERROR) }}"
        @if($binding->key) wire:key="{{ $binding->key }}" @endif
        x-on:focusout="if (!$el.contains($event.relatedTarget)) { finishEdit(); signal('blur') }" x-on:keydown.enter="if ($event.target !== $refs.field) { finishEdit(); signal('keydown') }" role="group" {{ $attributes->whereDoesntStartWith('wire:model')->except(['class', 'wire:key', 'id', 'name', 'placeholder', 'invalid', ...array_keys($fieldAttributes->all())])->class('ui-input-otp-body') }}>
        @foreach ($groups as $group)
            @if (! $loop->first)
                <span wire:key="separator-{{ $group[0] }}" class="ui-input-otp-separator" aria-hidden="true"><x-ui::icon name="minus" /></span>
            @endif
            <div wire:key="group-{{ $group[0] }}" class="ui-input-otp-group {{ $groupClass }}">
            @foreach ($group as $index)
            <span wire:key="cell-{{ $index }}" class="ui-input-otp-cell">
            <input type="text" class="ui-input-otp-slot {{ $cellClass }}" data-otp-index="{{ $index }}" data-start="{{ $loop->first ? 'true' : 'false' }}" data-end="{{ $loop->last ? 'true' : 'false' }}" inputmode="{{ $pattern === 'numeric' ? 'numeric' : 'text' }}"
                placeholder=" "
                autocomplete="{{ $index === 0 ? 'one-time-code' : 'off' }}" maxlength="{{ $length }}"
                value="{{ ($binding->config['value'] ?? '')[$index] ?? '' }}"
                aria-label="{{ __('ui::ui.code_position', ['label' => $attributes->get('aria-label', __('ui::ui.code')), 'current' => $index + 1, 'total' => $length]) }}"
                {{ $fieldAttributes->except('aria-label')->merge(['id' => $binding->inputId($index), 'aria-invalid' => $attributes->get('invalid') ? 'true' : null]) }}
                x-on:input.stop="input($event, {{ $index }})" x-on:paste.prevent="insert($event.clipboardData.getData('text'), {{ $index }})"
                x-on:keydown="key($event, {{ $index }})" x-on:focus="$el.select()" @disabled($disabled) @readonly($readonly)>
            <span class="ui-input-otp-caret" aria-hidden="true"></span>
            </span>
            @endforeach
            </div>
        @endforeach
        <input type="text" hidden tabindex="-1" aria-hidden="true" autocomplete="off" x-ref="field" wire:key="otp-value-{{ $binding->key }}" {{ $attributes->only(['name', 'form'])->merge($attributes->whereStartsWith('wire:model')->all()) }}
            value="{{ $binding->config['value'] }}" @disabled($disabled)>
    </div>
    {{ $slot }}
</{{ $binding->containerTag }}>
